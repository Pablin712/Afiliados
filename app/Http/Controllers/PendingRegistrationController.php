<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PendingRegistrationController extends Controller
{
    public function index(): View
    {
        $payments = Payment::query()
            ->with(['user', 'transaction.bank'])
            ->where('state', 'pending')
            ->latest('created_at')
            ->paginate(15);

        return view('admin.pending-registrations.index', [
            'payments' => $payments,
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
                $amount = (float) $payment->amount;
                $amountNow = $amountPrevious + $amount;

                $transaction->amount_previous = $amountPrevious;
                $transaction->amount = $amount;
                $transaction->amount_now = $amountNow;
                $transaction->is_annulled = false;
                $transaction->detail = __('messages.admin.approved_registration_transaction_detail', [
                    'user' => $payment->user->name,
                ]);
                $transaction->save();

                $bank->amount = $amountNow;
                $bank->save();
            }

            $payment->state = 'approved';
            $payment->reviewed_by = Auth::id();
            $payment->reviewed_at = now();
            $payment->save();

            $user = $payment->user;
            $user->approved_at = now();
            $user->save();

            $customerType = MembershipType::query()
                ->where('name', 'customer')
                ->firstOrFail();

            Membership::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'membership_type_id' => $customerType->id,
                    'status' => 'active',
                    'started_at' => now(),
                    'expires_at' => now()->addMonths(2),
                    'last_payment_id' => $payment->id,
                ]
            );
        });

        return back()->with('status', __('messages.admin.pending_registration_approved'));
    }
}
