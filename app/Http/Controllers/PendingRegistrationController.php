<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PendingPaymentReviewService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class PendingRegistrationController extends Controller
{
    public function __construct(
        private readonly PendingPaymentReviewService $pendingPaymentReviewService,
    )
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $perPage   = max(5, min(100, (int) $request->integer('per_page', 15)));
        $search    = trim((string) $request->input('search', ''));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $tab       = strtolower((string) $request->input('tab', 'pending'));

        if ($tab === 'history') {
            $sortBy = $this->resolveHistorySortBy((string) $request->input('sort_by', 'created_at'));

            $query = Payment::query()
                ->with(['user', 'transaction.bank', 'reviewer']);

            if ($search !== '') {
                $query->where(function (Builder $q) use ($search): void {
                    $q->whereHas('user', function (Builder $inner) use ($search): void {
                        $inner->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                    })->orWhere('number', 'like', "%{$search}%");
                });
            }

            $records = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

            return response()->json([
                'html'          => view('admin.pending-registrations.partials.history-rows', ['records' => $records->items()])->render(),
                'total_records' => $records->total(),
                'current_page'  => $records->currentPage(),
                'per_page'      => $records->perPage(),
            ]);
        }

        $sortBy = $this->resolveSortBy((string) $request->input('sort_by', 'created_at'));

        $query = Payment::query()
            ->with(['user', 'transaction.bank'])
            ->where('state', 'pending');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->whereHas('user', function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('number', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        if ($request->boolean('ajax')) {
            return response()->json([
                'html'          => view('admin.pending-registrations.partials.table-rows', ['records' => $records->items()])->render(),
                'total_records' => $records->total(),
                'current_page'  => $records->currentPage(),
                'per_page'      => $records->perPage(),
            ]);
        }

        $historyTotal = Payment::query()->count();

        return view('admin.pending-registrations.index', [
            'records'      => $records,
            'totalRecords' => $records->total(),
            'historyTotal' => $historyTotal,
        ]);
    }

    public function approve(Payment $payment): RedirectResponse
    {
        if ($payment->state !== 'pending') {
            return back()->with('status', __('messages.admin.pending_registration_already_processed'));
        }

        try {
            $this->pendingPaymentReviewService->approve($payment, Auth::id());
        } catch (RuntimeException) {
            return back()->with('status', __('messages.admin.pending_registration_already_processed'));
        }

        return back()->with('status', __('messages.admin.pending_registration_approved'));
    }

    public function reject(Payment $payment): RedirectResponse
    {
        if ($payment->state !== 'pending') {
            return back()->with('status', __('messages.admin.pending_registration_already_processed'));
        }

        try {
            $this->pendingPaymentReviewService->reject($payment, Auth::id());
        } catch (RuntimeException) {
            return back()->with('status', __('messages.admin.pending_registration_already_processed'));
        }

        return back()->with('status', __('messages.admin.pending_registration_rejected'));
    }

    protected function resolveSortBy(string $requested): string
    {
        $allowed = ['created_at', 'amount', 'number'];

        return in_array($requested, $allowed, true) ? $requested : 'created_at';
    }

    protected function resolveHistorySortBy(string $requested): string
    {
        $allowed = ['created_at', 'amount', 'number', 'state', 'reviewed_at'];

        return in_array($requested, $allowed, true) ? $requested : 'created_at';
    }
}
