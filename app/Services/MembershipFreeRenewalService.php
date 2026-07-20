<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonInterface;

class MembershipFreeRenewalService
{
    /**
     * Whether the membership's current billing period already earned a free renewal: the
     * sponsor referred enough direct affiliates who made their first-ever approved payment
     * inside that same period. Reactivations don't count.
     *
     * The evaluation window is capped to the last month before expires_at, even if
     * started_at is older — started_at can go stale relative to the real monthly cadence
     * (e.g. an admin manually pushing expires_at forward without touching started_at), which
     * would otherwise let old referrals from a prior period count again toward a later
     * renewal decision they were never meant to cover.
     */
    public function qualifies(Membership $membership): bool
    {
        if ($membership->expires_at === null) {
            return false;
        }

        $periodEnd = $membership->expires_at;
        $periodStart = $periodEnd->copy()->subMonth();

        if ($membership->started_at !== null && $membership->started_at->gt($periodStart)) {
            $periodStart = $membership->started_at;
        }

        return $this->newCustomerReferralsInPeriod(
            (int) $membership->user_id,
            $periodStart,
            $periodEnd
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
