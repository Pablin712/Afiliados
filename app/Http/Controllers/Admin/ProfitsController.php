<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Profit;
use App\Services\ProfitPayoutService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitsController extends Controller
{
    public function __construct(private readonly ProfitPayoutService $profitPayoutService)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'state' => ['nullable', 'in:pending,made'],
            'search' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = Profit::query()->with(['user', 'userBank', 'transaction.bank', 'payer']);

        if (isset($validated['state'])) {
            $query->where('state', $validated['state']);
        }

        if (! empty($validated['search'])) {
            $search = trim((string) $validated['search']);
            $query->where(function (Builder $inner) use ($search): void {
                $inner->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (isset($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (isset($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $records = $query
            ->orderByRaw("CASE WHEN state = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.profits.index', [
            'records' => $records,
            'filters' => [
                'state' => $validated['state'] ?? '',
                'search' => $validated['search'] ?? '',
                'from' => $validated['from'] ?? '',
                'to' => $validated['to'] ?? '',
            ],
            'banks' => Bank::query()->select('id', 'name', 'number', 'amount')->orderBy('name')->get(),
            'pendingTotal' => (float) Profit::query()->where('state', 'pending')->sum('amount'),
            'paidTotal' => (float) Profit::query()->where('state', 'made')->sum('amount'),
        ]);
    }

    public function markAsPaid(Request $request, Profit $profit): RedirectResponse
    {
        $validated = $request->validate([
            'bank_id' => ['required', 'integer', 'exists:banks,id'],
            'detail' => ['nullable', 'string', 'max:500'],
        ]);

        $this->profitPayoutService->markAsPaid(
            $profit,
            (int) $validated['bank_id'],
            (int) $request->user()->id,
            isset($validated['detail']) ? trim((string) $validated['detail']) : null
        );

        return back()->with('status', __('messages.admin.profits.marked_as_paid'));
    }
}
