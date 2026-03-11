<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PendingRegistrationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $perPage   = max(5, min(100, (int) $request->integer('per_page', 15)));
        $search    = trim((string) $request->input('search', ''));
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortBy    = $this->resolveSortBy((string) $request->input('sort_by', 'created_at'));

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

        return view('admin.pending-registrations.index', [
            'records'      => $records,
            'totalRecords' => $records->total(),
        ]);
    }

    public function approve(Payment $payment): RedirectResponse
    {
        if ($payment->state !== 'pending') {
            return back()->with('status', __('messages.admin.pending_registration_already_processed'));
        }

        DB::transaction(function () use ($payment): void {
            $payment->load(['user', 'transaction.bank']);

            $transaction = $payment->transaction;
            if ($transaction instanceof Transaction) {
                $bank = $transaction->bank()->lockForUpdate()->firstOrFail();

                $amountPrevious = (float) $bank->amount;
                $amount         = (float) $payment->amount;
                $amountNow      = $amountPrevious + $amount;

                $transaction->amount_previous = $amountPrevious;
                $transaction->amount          = $amount;
                $transaction->amount_now      = $amountNow;
                $transaction->is_annulled     = false;
                $transaction->detail          = __('messages.admin.approved_registration_transaction_detail', [
                    'user' => $payment->user->name,
                ]);
                $transaction->save();

                $bank->amount = $amountNow;
                $bank->save();
            }

            $payment->state       = 'approved';
            $payment->reviewed_by = Auth::id();
            $payment->reviewed_at = now();
            $payment->save();

            $user             = $payment->user;
            $user->approved_at = now();
            $user->save();

            $customerType = MembershipType::query()
                ->where('name', 'customer')
                ->firstOrFail();

            Membership::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'membership_type_id' => $customerType->id,
                    'status'             => 'active',
                    'started_at'         => now(),
                    'expires_at'         => now()->addMonths(2),
                    'last_payment_id'    => $payment->id,
                ]
            );
        });

        return back()->with('status', __('messages.admin.pending_registration_approved'));
    }

    public function reject(Payment $payment): RedirectResponse
    {
        if ($payment->state !== 'pending') {
            return back()->with('status', __('messages.admin.pending_registration_already_processed'));
        }

        $payment->state       = 'rejected';
        $payment->reviewed_by = Auth::id();
        $payment->reviewed_at = now();
        $payment->save();

        return back()->with('status', __('messages.admin.pending_registration_rejected'));
    }

    protected function resolveSortBy(string $requested): string
    {
        $allowed = ['created_at', 'amount', 'number'];

        return in_array($requested, $allowed, true) ? $requested : 'created_at';
    }
}