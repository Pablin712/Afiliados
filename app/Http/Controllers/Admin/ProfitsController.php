<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profits\IndexProfitsRequest;
use App\Models\Bank;
use App\Models\Profit;
use App\Models\User;
use App\Services\ProfitPayoutService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

        $groupedRecords = DB::table('profits')
            ->select([
                'profits.user_id',
                'users.name as user_name',
                'users.email as user_email',
                DB::raw('SUM(profits.amount) as total_amount'),
                DB::raw('COUNT(profits.id) as profits_count'),
            ])
            ->leftJoin('users', 'users.id', '=', 'profits.user_id')
            ->where('profits.state', 'pending')
            ->groupBy('profits.user_id', 'users.name', 'users.email')
            ->orderByDesc(DB::raw('SUM(profits.amount)'))
            ->get();

        $userIds = $groupedRecords->pluck('user_id')->filter()->values();
        $usersWithBanks = User::query()
            ->whereIn('id', $userIds)
            ->with('userBanks')
            ->get()
            ->keyBy('id');

        $groupedRecords = $groupedRecords->map(function (object $row) use ($usersWithBanks): object {
            $row->userBanks = $usersWithBanks->get($row->user_id)?->userBanks ?? collect();
            return $row;
        });

        return view('admin.profits.index', [
            'records'        => $records,
            'totalRecords'   => $records->total(),
            'filters'        => $filters,
            'banks'          => Bank::query()->select('id', 'name', 'number', 'amount')->orderBy('name')->get(),
            'pendingTotal'   => (float) Profit::query()->where('state', 'pending')->sum('amount'),
            'paidTotal'      => (float) Profit::query()->where('state', 'made')->sum('amount'),
            'groupedRecords' => $groupedRecords,
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
            ->with(['user', 'user.userBanks', 'userBank', 'transaction.bank', 'payer'])
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

    public function markAllAsPaid(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'bank_id' => ['required', 'integer', 'exists:banks,id'],
            'detail'  => ['nullable', 'string', 'max:500'],
        ]);

        $pendingProfits = Profit::query()
            ->where('user_id', $user->id)
            ->where('state', 'pending')
            ->get();

        if ($pendingProfits->isEmpty()) {
            return back()->with('status', __('messages.admin.profits.all_marked_as_paid'));
        }

        $totalAmount = (float) $pendingProfits->sum('amount');
        $detail = isset($validated['detail']) ? trim((string) $validated['detail']) : null;

        foreach ($pendingProfits as $profit) {
            $this->profitPayoutService->markAsPaid(
                $profit,
                (int) $validated['bank_id'],
                (int) $request->user()->id,
                $detail
            );
        }

        $this->notifyUserPaid($user, $totalAmount);

        return back()->with('status', __('messages.admin.profits.all_marked_as_paid'));
    }

    public function markAsPaid(Request $request, Profit $profit): RedirectResponse
    {
        $validated = $request->validate([
            'bank_id' => ['required', 'integer', 'exists:banks,id'],
            'detail' => ['nullable', 'string', 'max:500'],
        ]);

        $paid = $this->profitPayoutService->markAsPaid(
            $profit,
            (int) $validated['bank_id'],
            (int) $request->user()->id,
            isset($validated['detail']) ? trim((string) $validated['detail']) : null
        );

        $this->notifyUserPaid($paid->user, (float) $paid->amount);

        return back()->with('status', __('messages.admin.profits.marked_as_paid'));
    }

    private function notifyUserPaid(?User $user, float $amount): void
    {
        if (! $user instanceof User || ! $user->phone) {
            return;
        }

        $message = "Hola *{$user->name}*, tu solicitud de cobro de ganancias por un monto de *\$"
            . number_format($amount, 2)
            . '* ha sido procesada y pagada por el administrador. Revisa tu cuenta bancaria';

        try {
            Http::withHeaders(['apiKey' => 'DAF579E359CA-43AA-959F-16EE1ED51F7A'])
                ->post('https://evoapi.abigailsoft.com/message/sendText/AET-SAS', [
                    'number' => $user->phone,
                    'text'   => $message,
                ]);
        } catch (\Throwable) {
            // La notificación no debe interrumpir el flujo de pago
        }
    }
}
