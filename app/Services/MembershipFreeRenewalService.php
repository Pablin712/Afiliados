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
     * sponsor referred enough direct affiliates who made their first-ever approved "customer"
     * payment inside that same period. Reactivations don't count.
     *
     * This is the single source of truth for the free-renewal rule — both the automatic
     * expiry processing (MembershipExpiryService) and the user-facing self-service button
     * (PlansController::renewForFree) must go through this method so they can never disagree
     * on what counts as "3 new customers this period".
     */
    public function qualifies(Membership $membership): bool
    {
        return $this->currentPeriodReferralCount($membership) >= $this->requiredNewCustomers();
    }

    /**
     * How many qualifying new-customer referrals the sponsor has in their current period —
     * exposed separately from qualifies() so callers can display progress (e.g. "2 of 3").
     */
    public function currentPeriodReferralCount(Membership $membership): int
    {
        if ($membership->expires_at === null) {
            return 0;
        }

        [$periodStart, $periodEnd] = $this->resolvePeriodBounds($membership);

        return $this->newCustomerReferralsInPeriod((int) $membership->user_id, $periodStart, $periodEnd);
    }

    /**
     * The evaluation window is capped to the last month before expires_at (2 months for the
     * still-ongoing first period, since that one legitimately spans 2 months) — even if
     * started_at is older than that. started_at can go stale relative to the real monthly
     * cadence (e.g. an admin manually pushing expires_at forward without touching started_at),
     * which would otherwise let old referrals from a prior period count again toward a later
     * renewal decision they were never meant to cover.
     *
     * Whether this is still the first period is read from renewal_count rather than inferred
     * from the started_at/expires_at gap: a stale started_at from the bug above can produce
     * the same wide gap as a genuine first period, so date arithmetic alone can't tell them
     * apart — renewal_count is explicit, incremented every time a period actually closes
     * (paid or free), so it can't be fooled by a date that was never touched.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function resolvePeriodBounds(Membership $membership): array
    {
        $periodEnd = $membership->expires_at;
        $maxWindowMonths = ((int) $membership->renewal_count) === 0 ? 2 : 1;
        $periodStart = $periodEnd->copy()->subMonths($maxWindowMonths);

        if ($membership->started_at !== null && $membership->started_at->gt($periodStart)) {
            $periodStart = $membership->started_at;
        }

        return [$periodStart, $periodEnd];
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
                ->with('program.membershipType')
                ->where('user_id', $affiliateId)
                ->where('state', 'approved')
                ->orderBy('reviewed_at')
                ->orderBy('id')
                ->first();

            if (! $firstApprovedPayment || $firstApprovedPayment->reviewed_at === null) {
                continue;
            }

            // Only "customer" tier programs count as the new-customer referral the business
            // rule is about (defense in depth: today every program is customer-tier, but
            // nothing in the schema guarantees that stays true).
            $typeName = strtolower((string) ($firstApprovedPayment->program?->membershipType?->name ?? ''));
            if ($typeName !== '' && $typeName !== 'customer') {
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
