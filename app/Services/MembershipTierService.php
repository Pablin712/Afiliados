<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MembershipTierService
{
    /**
     * @return array<string, mixed>
     */
    public function recalculate(?int $userId = null, bool $dryRun = false): array
    {
        $types = MembershipType::query()->get();

        $freeType = $types->first(fn (MembershipType $type): bool => strtolower((string) $type->name) === 'free');
        $customerType = $types->first(fn (MembershipType $type): bool => strtolower((string) $type->name) === 'customer');

        $progressionTypes = $types
            ->filter(fn (MembershipType $type): bool => ! in_array(strtolower((string) $type->name), ['free', 'customer'], true))
            ->filter(fn (MembershipType $type): bool => (int) $type->affiliates_required > 0)
            ->sortBy('affiliates_required')
            ->values();

        $directActiveAffiliates = DB::table('users as child')
            ->join('memberships as m', 'm.user_id', '=', 'child.id')
            ->join('membership_types as mt', 'mt.id', '=', 'm.membership_type_id')
            ->where('m.status', 'active')
            ->whereRaw('LOWER(mt.name) <> ?', ['free'])
            ->groupBy('child.sponsor_id')
            ->select('child.sponsor_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'child.sponsor_id');

        $query = Membership::query()
            ->with(['membershipType', 'user.roles'])
            ->whereIn('status', ['active', 'free']);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $memberships = $query->get();

        $processed = 0;
        $changed = 0;
        $details = [];

        foreach ($memberships as $membership) {
            $user = $membership->user;
            if ($user instanceof User && $user->hasRole('admin')) {
                continue;
            }

            $processed++;

            $activeAffiliates = (int) ($directActiveAffiliates[$membership->user_id] ?? 0);
            $targetType = $this->resolveTargetType(
                $membership,
                $activeAffiliates,
                $freeType,
                $customerType,
                $progressionTypes
            );

            if (! $targetType instanceof MembershipType) {
                continue;
            }

            $targetStatus = strtolower((string) $targetType->name) === 'free' ? 'free' : 'active';
            $typeChanged = (int) $membership->membership_type_id !== (int) $targetType->id;
            $statusChanged = (string) $membership->status !== $targetStatus;

            if (! $typeChanged && ! $statusChanged) {
                continue;
            }

            $changed++;

            $details[] = [
                'user_id' => (int) $membership->user_id,
                'active_direct_affiliates' => $activeAffiliates,
                'from' => [
                    'type' => strtolower((string) ($membership->membershipType?->name ?? 'unknown')),
                    'status' => (string) $membership->status,
                ],
                'to' => [
                    'type' => strtolower((string) $targetType->name),
                    'status' => $targetStatus,
                ],
            ];

            if (! $dryRun) {
                $membership->membership_type_id = (int) $targetType->id;
                $membership->status = $targetStatus;
                $membership->save();
            }
        }

        return [
            'processed' => $processed,
            'changed' => $changed,
            'dry_run' => $dryRun,
            'details' => $details,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inspectUser(User $user): array
    {
        $user->loadMissing(['membership.membershipType']);

        $activeDirectAffiliates = (int) DB::table('users as child')
            ->join('memberships as m', 'm.user_id', '=', 'child.id')
            ->join('membership_types as mt', 'mt.id', '=', 'm.membership_type_id')
            ->where('child.sponsor_id', $user->id)
            ->where('m.status', 'active')
            ->whereRaw('LOWER(mt.name) <> ?', ['free'])
            ->count();

        $types = MembershipType::query()->get();
        $freeType = $types->first(fn (MembershipType $type): bool => strtolower((string) $type->name) === 'free');
        $customerType = $types->first(fn (MembershipType $type): bool => strtolower((string) $type->name) === 'customer');
        $progressionTypes = $types
            ->filter(fn (MembershipType $type): bool => ! in_array(strtolower((string) $type->name), ['free', 'customer'], true))
            ->filter(fn (MembershipType $type): bool => (int) $type->affiliates_required > 0)
            ->sortBy('affiliates_required')
            ->values();

        $membership = $user->membership;
        $currentTypeName = strtolower((string) ($membership?->membershipType?->name ?? 'unknown'));
        $currentStatus = (string) ($membership?->status ?? 'none');

        $targetType = $membership instanceof Membership
            ? $this->resolveTargetType($membership, $activeDirectAffiliates, $freeType, $customerType, $progressionTypes)
            : null;

        $targetTypeName = strtolower((string) ($targetType?->name ?? 'unknown'));
        $targetStatus = $targetTypeName === 'free' ? 'free' : 'active';

        return [
            'user_id' => (int) $user->id,
            'current' => [
                'type' => $currentTypeName,
                'status' => $currentStatus,
            ],
            'active_direct_affiliates' => $activeDirectAffiliates,
            'target' => [
                'type' => $targetTypeName,
                'status' => $targetStatus,
            ],
        ];
    }

    private function resolveTargetType(
        Membership $membership,
        int $activeAffiliates,
        ?MembershipType $freeType,
        ?MembershipType $customerType,
        Collection $progressionTypes
    ): ?MembershipType {
        if ((string) $membership->status === 'free') {
            return $freeType ?? $customerType;
        }

        $highestReached = $progressionTypes
            ->filter(fn (MembershipType $type): bool => $activeAffiliates >= (int) $type->affiliates_required)
            ->sortByDesc('affiliates_required')
            ->first();

        if ($highestReached instanceof MembershipType) {
            return $highestReached;
        }

        return $customerType ?? $freeType;
    }
}
