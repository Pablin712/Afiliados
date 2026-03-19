<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profits\IndexProfitsRequest;
use App\Models\Bank;
use App\Models\Profit;
use App\Services\ProfitPayoutService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitsController extends Controller
{
    public function __construct(private readonly ProfitPayoutService $profitPayoutService)
    {
    }

    public function index(IndexProfitsRequest $request): View|JsonResponse
    {
        $query = $this->buildQuery($request);

        $perPage = max(5, min(100, (int) $request->integer('per_page', 10)));
        $records = (clone $query)->paginate($perPage)->withQueryString();

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('admin.profits.partials.table-rows', ['records' => $records->items()])->render(),
                'total_records' => $records->total(),
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
            ]);
        }

        $filters = [
            'state' => (string) $request->input('state', ''),
            'search' => (string) $request->input('search', ''),
            'from' => (string) $request->input('from', ''),
            'to' => (string) $request->input('to', ''),
        ];

        return view('admin.profits.index', [
            'records' => $records,
            'totalRecords' => $records->total(),
            'filters' => $filters,
            'banks' => Bank::query()->select('id', 'name', 'number', 'amount')->orderBy('name')->get(),
            'pendingTotal' => (float) Profit::query()->where('state', 'pending')->sum('amount'),
            'paidTotal' => (float) Profit::query()->where('state', 'made')->sum('amount'),
        ]);
    }

    protected function buildQuery(IndexProfitsRequest $request): Builder
    {
        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'created_at'));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $state = trim((string) $request->input('state', ''));
        $search = trim((string) $request->input('search', ''));
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));

        $query = Profit::query()
            ->select('profits.*')
            ->with(['user', 'userBank', 'transaction.bank', 'payer'])
            ->leftJoin('users', 'users.id', '=', 'profits.user_id')
            ->leftJoin('user_banks', 'user_banks.id', '=', 'profits.user_bank_id');

        if ($state !== '') {
            $query->where('profits.state', $state);
        }

        if ($search !== '') {
            $query->where(function (Builder $inner) use ($search): void {
                $inner->where('profits.id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('user_banks.bank_name', 'like', "%{$search}%")
                    ->orWhere('user_banks.number', 'like', "%{$search}%");
            });
        }

        if ($from !== '') {
            $query->whereDate('profits.created_at', '>=', $from);
        }

        if ($to !== '') {
            $query->whereDate('profits.created_at', '<=', $to);
        }

        return $query
            ->orderByRaw("CASE WHEN profits.state = 'pending' THEN 0 ELSE 1 END")
            ->orderBy($sortBy, $sortOrder)
            ->orderByDesc('profits.id');
    }

    protected function resolveSortBy(string $requestedSortBy): string
    {
        $allowed = [
            'profits.id',
            'users.name',
            'user_banks.bank_name',
            'profits.amount',
            'profits.state',
            'profits.created_at',
            'profits.paid_at',
        ];

        $map = [
            'id' => 'profits.id',
            'user_name' => 'users.name',
            'bank_name' => 'user_banks.bank_name',
            'amount' => 'profits.amount',
            'state' => 'profits.state',
            'created_at' => 'profits.created_at',
            'paid_at' => 'profits.paid_at',
        ];

        $normalized = $map[$requestedSortBy] ?? (str_contains($requestedSortBy, '.') ? $requestedSortBy : 'profits.'.$requestedSortBy);

        return in_array($normalized, $allowed, true) ? $normalized : 'profits.created_at';
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
