<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonInterface;

class MembershipFreeRenewalService
{
    /**
     * Whether the membership's current billing period (started_at -> expires_at) already
     * earned a free renewal: the sponsor referred enough direct affiliates who made their
     * first-ever approved payment inside that same period. Reactivations don't count.
     */
    public function qualifies(Membership $membership): bool
    {
        if ($membership->started_at === null || $membership->expires_at === null) {
            return false;
        }

        return $this->newCustomerReferralsInPeriod(
            (int) $membership->user_id,
            $membership->started_at,
            $membership->expires_at
        ) >= $this->requiredNewCustomers();
    }

    public function newCustomerReferralsInPeriod(int $sponsorId, CarbonInterface $periodStart, CarbonInterface $periodEnd): int
    {
        $directAffiliateIds = User::query()
            ->where('sponsor_id', $sponsorId)
            ->where('id', '!=', $sponsorId)
            ->pluck('id');

        if ($directAffiliateIds->isEmpty()) {
            return 0;
        }

        $count = 0;

        foreach ($directAffiliateIds as $affiliateId) {
            $firstApprovedPayment = Payment::query()
                ->where('user_id', $affiliateId)
                ->where('state', 'approved')
                ->orderBy('reviewed_at')
                ->orderBy('id')
                ->first();

            if (! $firstApprovedPayment || $firstApprovedPayment->reviewed_at === null) {
                continue;
            }

            $reviewedAt = $firstApprovedPayment->reviewed_at;

            if ($reviewedAt->gte($periodStart) && $reviewedAt->lt($periodEnd)) {
                $count++;
            }
        }

        return $count;
    }

    public function requiredNewCustomers(): int
    {
        return max(1, (int) config('affiliates.free_renewal.required_new_customers', 3));
    }
}
