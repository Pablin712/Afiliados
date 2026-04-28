<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MembershipTierService
{
    /**
     * @return array<string, mixed>
     */
    public function recalculate(?int $userId = null, bool $dryRun = false): array
    {
        $types = MembershipType::query()->get();
        $users = User::query()
            ->with(['membership.membershipType', 'roles'])
            ->get();

        $usersById = $users->keyBy('id');
        $typeMap = $this->buildTypeMap($types);

        $childrenBySponsor = [];
        foreach ($users as $user) {
            $sponsorId = (int) ($user->sponsor_id ?? 0);

            if ($sponsorId > 0 && $sponsorId !== (int) $user->id) {
                $childrenBySponsor[$sponsorId][] = (int) $user->id;
            }
        }

        $rankRules = $this->rankRules();
        $pointsPerAffiliate = max(1, (int) config('affiliates.points_per_affiliate', 100));

        $descendantCache = [];
        $rankCache = [];
        $rankResolving = [];

        $query = Membership::query()
            ->with(['membershipType', 'user.roles'])
            ->whereIn('status', ['active', 'free', 'expired', 'pending_payment']);

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

            $directActiveAffiliates = $this->countDirectQualifyingAffiliates((int) $membership->user_id, $childrenBySponsor, $usersById);
            $teamActiveAffiliates = $this->countQualifyingDescendants(
                (int) $membership->user_id,
                $childrenBySponsor,
                $usersById,
                $descendantCache,
                []
            );
            $teamPoints = $teamActiveAffiliates * $pointsPerAffiliate;
            $rankIndex = $this->resolveRankIndexForUser(
                (int) $membership->user_id,
                $usersById,
                $childrenBySponsor,
                $rankRules,
                $pointsPerAffiliate,
                $descendantCache,
                $rankCache,
                $rankResolving
            );

            $targetType = $this->resolveTargetType(
                $membership,
                $rankIndex,
                $typeMap
            );

            if (! $targetType instanceof MembershipType) {
                continue;
            }

            $targetStatus = strtolower((string) $targetType->name) === 'free'
                ? 'free'
                : ((string) $membership->status === 'free' ? 'active' : (string) $membership->status);
            $typeChanged = (int) $membership->membership_type_id !== (int) $targetType->id;
            $statusChanged = (string) $membership->status !== $targetStatus;

            if (! $typeChanged && ! $statusChanged) {
                continue;
            }

            $changed++;

            $details[] = [
                'user_id' => (int) $membership->user_id,
                'active_direct_affiliates' => $directActiveAffiliates,
                'team_points' => $teamPoints,
                'rank_index' => $rankIndex,
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
        $user->loadMissing(['membership.membershipType', 'roles']);

        $types = MembershipType::query()->get();
        $typeMap = $this->buildTypeMap($types);

        $allUsers = User::query()->with(['membership.membershipType', 'roles'])->get();
        $usersById = $allUsers->keyBy('id');

        $childrenBySponsor = [];
        foreach ($allUsers as $row) {
            $sponsorId = (int) ($row->sponsor_id ?? 0);

            if ($sponsorId > 0 && $sponsorId !== (int) $row->id) {
                $childrenBySponsor[$sponsorId][] = (int) $row->id;
            }
        }

        $rankRules = $this->rankRules();
        $pointsPerAffiliate = max(1, (int) config('affiliates.points_per_affiliate', 100));
        $descendantCache = [];
        $rankCache = [];
        $rankResolving = [];

        $activeDirectAffiliates = $this->countDirectQualifyingAffiliates((int) $user->id, $childrenBySponsor, $usersById);
        $teamActiveAffiliates = $this->countQualifyingDescendants((int) $user->id, $childrenBySponsor, $usersById, $descendantCache, []);
        $teamPoints = $teamActiveAffiliates * $pointsPerAffiliate;

        $rankIndex = $this->resolveRankIndexForUser(
            (int) $user->id,
            $usersById,
            $childrenBySponsor,
            $rankRules,
            $pointsPerAffiliate,
            $descendantCache,
            $rankCache,
            $rankResolving
        );

        $membership = $user->membership;
        $currentTypeName = strtolower((string) ($membership?->membershipType?->name ?? 'unknown'));
        $currentStatus = (string) ($membership?->status ?? 'none');

        $targetType = $membership instanceof Membership
            ? $this->resolveTargetType($membership, $rankIndex, $typeMap)
            : null;

        $targetTypeName = strtolower((string) ($targetType?->name ?? 'unknown'));
        $targetStatus = $targetTypeName === 'free' ? 'free' : ($currentStatus === 'free' ? 'free' : $currentStatus);

        return [
            'user_id' => (int) $user->id,
            'current' => [
                'type' => $currentTypeName,
                'status' => $currentStatus,
            ],
            'active_direct_affiliates' => $activeDirectAffiliates,
            'team_points' => $teamPoints,
            'rank_index' => $rankIndex,
            'target' => [
                'type' => $targetTypeName,
                'status' => $targetStatus,
            ],
        ];
    }

    private function resolveTargetType(
        Membership $membership,
        int $rankIndex,
        array $typeMap
    ): ?MembershipType {
        $currentStatus = strtolower((string) $membership->status);
        $currentType = strtolower((string) ($membership->membershipType?->name ?? ''));

        if ($currentStatus === 'free' || $currentType === 'free') {
            return $typeMap['free'] ?? $typeMap['customer'] ?? null;
        }

        if ($currentStatus !== 'active') {
            return $typeMap['customer'] ?? $typeMap['free'] ?? null;
        }

        if ($rankIndex <= 0) {
            return $typeMap['customer'] ?? $typeMap['free'] ?? null;
        }

        $rankName = $this->rankNameFromIndex($rankIndex);

        return $typeMap[$rankName] ?? $typeMap['customer'] ?? $typeMap['free'] ?? null;
    }

    /**
     * @param Collection<int, MembershipType> $types
     * @return array<string, MembershipType>
     */
    private function buildTypeMap(Collection $types): array
    {
        $map = [];

        foreach ($types as $type) {
            $normalized = Str::lower((string) $type->name);

            if ($normalized === '') {
                continue;
            }

            $map[$normalized] = $type;
        }

        if (! isset($map['professional']) && isset($map['proffesional'])) {
            $map['professional'] = $map['proffesional'];
        }

        if (! isset($map['proffesional']) && isset($map['professional'])) {
            $map['proffesional'] = $map['professional'];
        }

        return $map;
    }

    /**
     * @return array<string, array<string, int|null>>
     */
    private function rankRules(): array
    {
        /** @var array<string, array<string, int|null>> $rules */
        $rules = (array) config('affiliates.rank_rules', []);

        if ($rules !== []) {
            return $rules;
        }

        return [
            'beginner' => ['direct_affiliates' => 1, 'team_points' => 0, 'direct_rank_min' => null, 'direct_rank_count' => 0],
            'constructor' => ['direct_affiliates' => 3, 'team_points' => 0, 'direct_rank_min' => null, 'direct_rank_count' => 0],
            'explorer' => ['direct_affiliates' => 5, 'team_points' => 0, 'direct_rank_min' => null, 'direct_rank_count' => 0],
            'professional' => ['direct_affiliates' => 8, 'team_points' => 1200, 'direct_rank_min' => null, 'direct_rank_count' => 0],
            'elite' => ['direct_affiliates' => 10, 'team_points' => 2000, 'direct_rank_min' => 2, 'direct_rank_count' => 2],
            'master' => ['direct_affiliates' => 12, 'team_points' => 4000, 'direct_rank_min' => 4, 'direct_rank_count' => 2],
            'legend' => ['direct_affiliates' => 15, 'team_points' => 9000, 'direct_rank_min' => 4, 'direct_rank_count' => 3],
        ];
    }

    private function rankNameFromIndex(int $rankIndex): string
    {
        return match ($rankIndex) {
            1 => 'beginner',
            2 => 'constructor',
            3 => 'explorer',
            4 => 'professional',
            5 => 'elite',
            6 => 'master',
            7 => 'legend',
            default => 'customer',
        };
    }

    /**
     * @param array<int, array<int, int>> $childrenBySponsor
     * @param Collection<int, User> $usersById
     */
    private function countDirectQualifyingAffiliates(int $userId, array $childrenBySponsor, Collection $usersById): int
    {
        $count = 0;

        foreach ($childrenBySponsor[$userId] ?? [] as $childId) {
            $child = $usersById->get($childId);

            if ($child instanceof User && $this->isQualifyingAffiliate($child)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param array<int, array<int, int>> $childrenBySponsor
     * @param Collection<int, User> $usersById
     * @param array<int, int> $cache
     * @param array<int, bool> $path
     */
    private function countQualifyingDescendants(
        int $userId,
        array $childrenBySponsor,
        Collection $usersById,
        array &$cache,
        array $path
    ): int {
        if (isset($cache[$userId])) {
            return $cache[$userId];
        }

        if (isset($path[$userId])) {
            return 0;
        }

        $path[$userId] = true;
        $total = 0;

        foreach ($childrenBySponsor[$userId] ?? [] as $childId) {
            $child = $usersById->get($childId);
            if (! $child instanceof User) {
                continue;
            }

            if ($this->isQualifyingAffiliate($child)) {
                $total++;
            }

            $total += $this->countQualifyingDescendants($childId, $childrenBySponsor, $usersById, $cache, $path);
        }

        $cache[$userId] = $total;

        return $total;
    }

    /**
     * @param array<int, array<int, int>> $childrenBySponsor
     * @param Collection<int, User> $usersById
     * @param array<string, array<string, int|null>> $rankRules
     * @param array<int, int> $descendantCache
     * @param array<int, int> $rankCache
     * @param array<int, bool> $rankResolving
     */
    private function resolveRankIndexForUser(
        int $userId,
        Collection $usersById,
        array $childrenBySponsor,
        array $rankRules,
        int $pointsPerAffiliate,
        array &$descendantCache,
        array &$rankCache,
        array &$rankResolving
    ): int {
        if (isset($rankCache[$userId])) {
            return $rankCache[$userId];
        }

        if (isset($rankResolving[$userId])) {
            return 0;
        }

        $user = $usersById->get($userId);
        if (! $user instanceof User || $user->hasRole('admin')) {
            $rankCache[$userId] = 0;

            return 0;
        }

        if (! $this->isQualifyingAffiliate($user)) {
            $rankCache[$userId] = 0;

            return 0;
        }

        $rankResolving[$userId] = true;

        $directActive = $this->countDirectQualifyingAffiliates($userId, $childrenBySponsor, $usersById);
        $teamPoints = $this->countQualifyingDescendants($userId, $childrenBySponsor, $usersById, $descendantCache, []) * $pointsPerAffiliate;

        $directRanks = [];
        foreach ($childrenBySponsor[$userId] ?? [] as $childId) {
            $child = $usersById->get($childId);
            if (! $child instanceof User || ! $this->isQualifyingAffiliate($child)) {
                continue;
            }

            $directRanks[] = $this->resolveRankIndexForUser(
                $childId,
                $usersById,
                $childrenBySponsor,
                $rankRules,
                $pointsPerAffiliate,
                $descendantCache,
                $rankCache,
                $rankResolving
            );
        }

        $rank = 0;

        foreach (['legend', 'master', 'elite', 'professional', 'explorer', 'constructor', 'beginner'] as $rankName) {
            $rule = $rankRules[$rankName] ?? [];

            $requiredDirect = (int) ($rule['direct_affiliates'] ?? 0);
            $requiredTeamPoints = (int) ($rule['team_points'] ?? 0);
            $requiredDirectRankMin = isset($rule['direct_rank_min']) ? (int) $rule['direct_rank_min'] : null;
            $requiredDirectRankCount = (int) ($rule['direct_rank_count'] ?? 0);

            if ($directActive < $requiredDirect || $teamPoints < $requiredTeamPoints) {
                continue;
            }

            if ($requiredDirectRankMin !== null && $requiredDirectRankCount > 0) {
                $qualifiedDirects = collect($directRanks)
                    ->filter(fn (int $childRank): bool => $childRank >= $requiredDirectRankMin)
                    ->count();

                if ($qualifiedDirects < $requiredDirectRankCount) {
                    continue;
                }
            }

            $rank = $this->rankIndexFromName($rankName);
            break;
        }

        unset($rankResolving[$userId]);
        $rankCache[$userId] = $rank;

        return $rank;
    }

    private function rankIndexFromName(string $name): int
    {
        return match (Str::lower($name)) {
            'beginner' => 1,
            'constructor' => 2,
            'explorer' => 3,
            'professional', 'proffesional' => 4,
            'elite' => 5,
            'master' => 6,
            'legend' => 7,
            default => 0,
        };
    }

    private function isQualifyingAffiliate(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return false;
        }

        $membership = $user->membership;
        if (! $membership instanceof Membership || (string) $membership->status !== 'active') {
            return false;
        }

        $typeName = Str::lower((string) ($membership->membershipType?->name ?? ''));

        return $typeName !== '' && $typeName !== 'free';
    }
}
