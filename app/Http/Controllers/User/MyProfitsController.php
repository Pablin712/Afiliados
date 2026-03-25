<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profits\IndexOwnProfitsRequest;
use App\Models\Profit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MyProfitsController extends Controller
{
    public function index(IndexOwnProfitsRequest $request): View|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = $this->buildQuery($request, $user);
        $perPage = max(5, min(100, (int) $request->integer('per_page', 10)));
        $records = (clone $query)->paginate($perPage)->withQueryString();

        if ($request->boolean('ajax')) {
            return response()->json([
                'html' => view('user.profits.partials.table-rows', ['records' => $records->items()])->render(),
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

        return view('user.profits.index', [
            'records' => $records,
            'totalRecords' => $records->total(),
            'filters' => $filters,
            'pendingTotal' => (float) Profit::query()->where('user_id', $user->id)->where('state', 'pending')->sum('amount'),
            'paidTotal' => (float) Profit::query()->where('user_id', $user->id)->where('state', 'made')->sum('amount'),
            'monthTotal' => (float) Profit::query()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
        ]);
    }

    protected function buildQuery(IndexOwnProfitsRequest $request, User $user): Builder
    {
        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'created_at'));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $state = trim((string) $request->input('state', ''));
        $search = trim((string) $request->input('search', ''));
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));

        $hasSourceUserId = Schema::hasColumn('profits', 'source_user_id');
        $hasSourcePaymentId = Schema::hasColumn('profits', 'source_payment_id');
        $query = Profit::query()
            ->select('profits.*')
            ->with(['sourceUser', 'sourcePayment'])
            ->where('profits.user_id', $user->id);

        if ($hasSourceUserId) {
            $query->leftJoin('users as source_users', 'source_users.id', '=', 'profits.source_user_id');
        }

        if ($state !== '') {
            $query->where('profits.state', $state);
        }

        if ($search !== '') {
            $query->where(function (Builder $inner) use ($search, $hasSourcePaymentId, $hasSourceUserId): void {
                $inner->where('profits.id', 'like', "%{$search}%")
                    ->when($hasSourcePaymentId, fn (Builder $q) => $q->orWhere('profits.source_payment_id', 'like', "%{$search}%"))
                    ->orWhere('profits.detail', 'like', "%{$search}%")
                    ->when($hasSourceUserId, fn (Builder $q) => $q->orWhere('source_users.name', 'like', "%{$search}%"))
                    ->when($hasSourceUserId, fn (Builder $q) => $q->orWhere('source_users.email', 'like', "%{$search}%"));
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
            'profits.amount',
            'profits.state',
            'profits.created_at',
            'profits.paid_at',
        ];

        if (Schema::hasColumn('profits', 'source_user_id')) {
            $allowed[] = 'source_users.name';
        }

        if (Schema::hasColumn('profits', 'source_payment_id')) {
            $allowed[] = 'profits.source_payment_id';
        }

        if (Schema::hasColumn('profits', 'source_level')) {
            $allowed[] = 'profits.source_level';
        }

        $map = [
            'id' => 'profits.id',
            'source_user_name' => 'source_users.name',
            'source_payment_id' => 'profits.source_payment_id',
            'source_level' => 'profits.source_level',
            'amount' => 'profits.amount',
            'state' => 'profits.state',
            'created_at' => 'profits.created_at',
            'paid_at' => 'profits.paid_at',
        ];

        $normalized = $map[$requestedSortBy] ?? (str_contains($requestedSortBy, '.') ? $requestedSortBy : 'profits.'.$requestedSortBy);

        return in_array($normalized, $allowed, true) ? $normalized : 'profits.created_at';
    }
}
