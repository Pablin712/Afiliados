<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PendingPaymentReviewService
{
    public function __construct(
        private readonly ProfitDistributionService $profitDistributionService,
        private readonly MembershipTierService $membershipTierService,
        private readonly RegistrationWhatsappService $registrationWhatsappService,
    ) {
    }

    public function approve(Payment $payment, ?int $reviewedBy = null, ?string $reviewDetail = null): void
    {
        if ($payment->state !== 'pending') {
            throw new RuntimeException('Payment is already processed.');
        }

        DB::transaction(function () use ($payment, $reviewedBy, $reviewDetail): void {
            $payment->load(['user', 'transaction.bank', 'program.membershipType']);

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
                $transaction->detail = $reviewDetail !== null && $reviewDetail !== ''
                    ? $reviewDetail
                    : __('messages.admin.approved_registration_transaction_detail', [
                        'user' => $payment->user->name,
                    ]);
                $transaction->save();

                $bank->amount = $amountNow;
                $bank->save();
            }

            $payment->state = 'approved';
            $payment->reviewed_by = $reviewedBy;
            $payment->reviewed_at = now();
            $payment->save();

            $user = $payment->user;
            $user->approved_at = now();
            $user->save();

            $membershipType = $payment->program?->membershipType;

            if ($membershipType === null) {
                $membershipType = MembershipType::query()
                    ->where('name', 'customer')
                    ->firstOrFail();
            }

            $currentMembership = Membership::query()
                ->with('membershipType')
                ->where('user_id', $user->id)
                ->first();

            $isFreeMembership = (string) ($currentMembership?->status ?? 'free') === 'free'
                || strtolower((string) ($currentMembership?->membershipType?->name ?? 'free')) === 'free';

            $durationMonths = $isFreeMembership ? 2 : 1;

            Membership::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'membership_type_id' => $membershipType->id,
                    'status' => 'active',
                    'started_at' => now(),
                    'expires_at' => now()->addMonths($durationMonths),
                    'last_payment_id' => $payment->id,
                ]
            );

            if ($isFreeMembership) {
                $this->registrationWhatsappService->sendPostPago($user);
            }

            $this->profitDistributionService->distributeForApprovedPayment($payment, $membershipType);

            if ((int) ($user->sponsor_id ?? 0) > 0) {
                $this->membershipTierService->recalculate((int) $user->sponsor_id);
            }
        });
    }

    public function reject(Payment $payment, ?int $reviewedBy = null): void
    {
        if ($payment->state !== 'pending') {
            throw new RuntimeException('Payment is already processed.');
        }

        $payment->state = 'rejected';
        $payment->reviewed_by = $reviewedBy;
        $payment->reviewed_at = now();
        $payment->save();
    }
}
