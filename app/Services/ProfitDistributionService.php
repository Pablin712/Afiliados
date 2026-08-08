<?php

namespace App\Services;

use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\User;

class ProfitDistributionService
{
    /**
     * "Programa de Socios AET": pays the buyer's direct sponsor a commission
     * equal to a percentage of the payment amount. The percentage is looked
     * up from the sponsor's points accumulated so far in the current
     * calendar year, BEFORE this sale (points are not stored; they are
     * counted live as 100 per approved payment made by a direct affiliate).
     * Full rules: docs/14-programa-socios-aet-anual.md.
     */
    public function distributeForApprovedPayment(Payment $payment, MembershipType $membershipType): void
    {
        if (strtolower((string) $membershipType->name) !== 'customer') {
            return;
        }

        $payment->loadMissing('user');
        $buyer = $payment->user;

        if (! $buyer instanceof User || (int) ($buyer->sponsor_id ?? 0) <= 0) {
            return;
        }

        $sponsor = User::query()->find($buyer->sponsor_id);

        if (! $sponsor instanceof User || $sponsor->id === $buyer->id || $sponsor->hasRole('admin')) {
            return;
        }

        $pointsBefore = $this->annualPointsBefore($sponsor, $payment);
        $percentage = $this->resolvePercentage($pointsBefore);

        if ($percentage <= 0) {
            return;
        }

        $amount = round((float) $payment->amount * $percentage, 2);

        if ($amount <= 0) {
            return;
        }

        $defaultBank = $sponsor->defaultUserBank()->first();

        Profit::query()->create([
            'user_id' => $sponsor->id,
            'user_bank_id' => $defaultBank?->id,
            'period_month' => now()->startOfMonth()->toDateString(),
            'source_payment_id' => $payment->id,
            'source_user_id' => $buyer->id,
            'source_level' => 1,
            'amount' => $amount,
            'state' => 'pending',
            'detail' => sprintf('direct_sale|%d|%d', (int) round($percentage * 100), $pointsBefore),
        ]);

        $sponsor->increment('commission_balance', $amount);
    }

    private function annualPointsBefore(User $sponsor, Payment $currentPayment): int
    {
        $pointsPerSale = (int) config('affiliates.annual_points_commission.points_per_sale', 100);

        $priorSalesCount = Payment::query()
            ->where('state', 'approved')
            ->where('id', '!=', $currentPayment->id)
            ->whereYear('reviewed_at', now()->year)
            ->whereIn('user_id', function ($query) use ($sponsor): void {
                $query->select('id')->from('users')->where('sponsor_id', $sponsor->id);
            })
            ->count();

        return $priorSalesCount * $pointsPerSale;
    }

    /**
     * @return float Commission percentage (e.g. 0.15 for 15%), 0 if no tier reached.
     */
    private function resolvePercentage(int $pointsBefore): float
    {
        $tiers = (array) config('affiliates.annual_points_commission.tiers', []);
        $percentage = 0.0;

        foreach ($tiers as $tier) {
            if ($pointsBefore >= (int) ($tier['min_points'] ?? PHP_INT_MAX)) {
                $percentage = (float) ($tier['percentage'] ?? $percentage);
            }
        }

        return $percentage;
    }
}
