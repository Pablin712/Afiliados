<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banks\IndexBanksRequest;
use App\Http\Requests\Banks\StoreBankRequest;
use App\Http\Requests\Banks\UpdateBankRequest;
use App\Models\Bank;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BanksController extends Controller
{
    public function index(IndexBanksRequest $request): View|JsonResponse
    {
        $query = $this->buildQuery($request);

        $perPage = max(5, min(100, (int) $request->integer('per_page', 10)));
        $records = (clone $query)->paginate($perPage)->withQueryString();

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('admin.banks.partials.table-rows', ['records' => $records->items()])->render(),
                'total_records' => $records->total(),
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
            ]);
        }

        return view('admin.banks.index', [
            'records' => $records,
            'totalRecords' => $records->total(),
        ]);
    }

    public function store(StoreBankRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['amount'] = (float) ($payload['amount'] ?? 0);

        Bank::query()->create($payload);

        return redirect()
            ->route('admin.banks.index')
            ->with('status', __('messages.admin.banks.messages.created'));
    }

    public function update(UpdateBankRequest $request, Bank $bank): RedirectResponse
    {
        $payload = $request->validated();
        $payload['amount'] = (float) ($payload['amount'] ?? 0);

        $bank->update($payload);

        return redirect()
            ->route('admin.banks.index')
            ->with('status', __('messages.admin.banks.messages.updated'));
    }

    public function destroy(Bank $bank): RedirectResponse
    {
        if ($bank->transactions()->exists()) {
            return redirect()
                ->route('admin.banks.index')
                ->with('error', __('messages.admin.banks.messages.delete_blocked'));
        }

        $bank->delete();

        return redirect()
            ->route('admin.banks.index')
            ->with('status', __('messages.admin.banks.messages.deleted'));
    }

    protected function buildQuery(IndexBanksRequest $request): Builder
    {
        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'name'));
        $sortOrder = strtolower((string) $request->input('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $search = trim((string) $request->input('search', ''));

        $query = Bank::query()->select('banks.*');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('banks.name', 'like', "%{$search}%")
                    ->orWhere('banks.owner', 'like', "%{$search}%")
                    ->orWhere('banks.identification', 'like', "%{$search}%")
                    ->orWhere('banks.number', 'like', "%{$search}%")
                    ->orWhere('banks.detail', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->orderByDesc('banks.id');
    }

    protected function resolveSortBy(string $requestedSortBy): string
    {
        $allowed = [
            'banks.id',
            'banks.name',
            'banks.owner',
            'banks.identification',
            'banks.number',
            'banks.amount',
            'banks.created_at',
        ];

        $normalized = str_contains($requestedSortBy, '.') ? $requestedSortBy : 'banks.'.$requestedSortBy;

        return in_array($normalized, $allowed, true) ? $normalized : 'banks.name';
    }
}
