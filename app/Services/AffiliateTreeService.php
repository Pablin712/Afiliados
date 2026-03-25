<?php

namespace App\Services;

use App\Models\Profit;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AffiliateTreeService
{
    /**
     * @return array<int, array{level:int,user:User}>
     */
    public function sponsorsByLevel(User $user, int $maxLevels = 3): array
    {
        $levels = [];
        $current = $user;

        for ($level = 1; $level <= $maxLevels; $level++) {
            $sponsorId = (int) ($current->sponsor_id ?? 0);
            if ($sponsorId <= 0 || $sponsorId === $current->id) {
                break;
            }

            $sponsor = User::query()->find($sponsorId);
            if (! $sponsor instanceof User) {
                break;
            }

            if ($this->isRootUser($sponsor)) {
                break;
            }

            $levels[] = [
                'level' => $level,
                'user' => $sponsor,
            ];

            $current = $sponsor;
        }

        return $levels;
    }

    /**
     * @return array<int, Collection<int, User>>
     */
    public function affiliatesByLevel(User $user, int $maxLevels = 3): array
    {
        $result = [];
        $frontier = collect([$user->id]);

        for ($level = 1; $level <= $maxLevels; $level++) {
            $next = User::query()
                ->whereIn('sponsor_id', $frontier->all())
                ->whereColumn('id', '!=', 'sponsor_id')
                ->get();

            $result[$level] = $next;

            if ($next->isEmpty()) {
                break;
            }

            $frontier = $next->pluck('id');
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildTree(?User $rootUser = null, int $maxDepth = 6): array
    {
        $root = $rootUser ?? $this->resolveRootUser();

        $users = User::query()
            ->with(['membership.membershipType'])
            ->orderBy('id')
            ->get();

        /** @var array<int, list<User>> $childrenBySponsor */
        $childrenBySponsor = [];

        foreach ($users as $row) {
            $childrenBySponsor[(int) $row->sponsor_id][] = $row;
        }

        return $this->nodeFromUser($root, $childrenBySponsor, 1, max(1, $maxDepth));
    }

    /**
     * @return array<string, mixed>
     */
    public function userInsights(User $user): array
    {
        $user->loadMissing(['membership.membershipType', 'payments' => fn ($q) => $q->where('state', 'approved')->latest('reviewed_at')]);

        $sponsors = $this->sponsorsByLevel($user, (int) config('affiliates.max_sponsor_levels', 3));
        $affiliates = $this->affiliatesByLevel($user, 3);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'sponsor_id' => $user->sponsor_id,
                'commission_balance' => (float) $user->commission_balance,
                'joined_at' => optional($user->created_at)->toDateTimeString(),
                'membership' => $user->membership?->membershipType?->name,
                'last_approved_payment_at' => optional($user->payments->first()?->reviewed_at)->toDateTimeString(),
            ],
            'sponsors' => collect($sponsors)->map(fn (array $row) => [
                'level' => $row['level'],
                'id' => $row['user']->id,
                'name' => $row['user']->name,
                'email' => $row['user']->email,
            ])->values()->all(),
            'affiliates' => collect($affiliates)->mapWithKeys(fn (Collection $rows, int $level) => [
                "level_{$level}" => $rows->map(fn (User $affiliate) => [
                    'id' => $affiliate->id,
                    'name' => $affiliate->name,
                    'email' => $affiliate->email,
                    'sponsor_id' => $affiliate->sponsor_id,
                ])->values()->all(),
            ])->all(),
        ];
    }

    /**
     * @return array{nodes: list<array<string, mixed>>, edges: list<array<string, int>>, viewer_id:int, sponsor_id:?int}
     */
    public function buildUserScopeGraph(User $viewer, int $maxDepth = 3): array
    {
        $viewer->loadMissing(['membership.membershipType', 'sponsor.membership.membershipType']);

        $descendants = collect($this->affiliatesByLevel($viewer, max(1, $maxDepth)))
            ->flatten(1)
            ->values();

        $users = collect([$viewer])
            ->merge($descendants)
            ->when(
                $viewer->sponsor && (int) $viewer->sponsor->id !== (int) $viewer->id,
                fn ($rows) => $rows->prepend($viewer->sponsor)
            )
            ->unique('id')
            ->values();

        $userIds = $users->pluck('id')->all();

        return [
            'viewer_id' => (int) $viewer->id,
            'sponsor_id' => $viewer->sponsor && (int) $viewer->sponsor->id !== (int) $viewer->id
                ? (int) $viewer->sponsor->id
                : null,
            'nodes' => $users->map(fn (User $user): array => [
                'id' => $user->id,
                'label' => $user->name,
                'email' => $user->email,
                'membership' => $user->membership?->membershipType?->name,
                'commission_balance' => (float) $user->commission_balance,
                'joined_at' => optional($user->created_at)->toDateTimeString(),
            ])->all(),
            'edges' => $users
                ->filter(fn (User $user): bool => in_array((int) $user->sponsor_id, $userIds, true) && (int) $user->id !== (int) $user->sponsor_id)
                ->map(fn (User $user): array => [
                    'from' => (int) $user->sponsor_id,
                    'to' => (int) $user->id,
                ])
                ->values()
                ->all(),
        ];
    }

    public function canAccessInUserScope(User $viewer, User $subject): bool
    {
        if ((int) $viewer->id === (int) $subject->id) {
            return true;
        }

        if ((int) ($viewer->sponsor_id ?? 0) === (int) $subject->id && (int) $subject->id !== (int) $viewer->id) {
            return true;
        }

        return $this->isDescendantOfViewer($viewer, $subject);
    }

    /**
     * @return array<string, mixed>
     */
    public function userScopeInsights(User $viewer, User $subject): array
    {
        $subject->loadMissing([
            'membership.membershipType',
            'payments' => fn ($q) => $q->where('state', 'approved')->latest('reviewed_at'),
        ]);

        $isSponsor = (int) ($viewer->sponsor_id ?? 0) === (int) $subject->id && (int) $subject->id !== (int) $viewer->id;
        $isViewer = (int) $viewer->id === (int) $subject->id;
        $isAffiliate = ! $isSponsor && ! $isViewer;

        $sponsors = $isSponsor
            ? collect()
            : collect($this->sponsorsByLevel($subject, (int) config('affiliates.max_sponsor_levels', 3)))
                ->filter(fn (array $row): bool => $this->canAccessInUserScope($viewer, $row['user']))
                ->values();

        $affiliates = $isSponsor
            ? []
            : collect($this->affiliatesByLevel($subject, 3))->mapWithKeys(function (Collection $rows, int $level) use ($viewer): array {
                $visibleRows = $rows
                    ->filter(fn (User $affiliate): bool => $this->isDescendantOfViewer($viewer, $affiliate))
                    ->values();

                return [
                    "level_{$level}" => $visibleRows->map(fn (User $affiliate) => [
                        'id' => $affiliate->id,
                        'name' => $affiliate->name,
                        'email' => $affiliate->email,
                        'sponsor_id' => $affiliate->sponsor_id,
                    ])->all(),
                ];
            })->all();

        return [
            'scope' => [
                'relation' => $isSponsor ? 'sponsor' : ($isViewer ? 'self' : 'affiliate'),
                'viewer_id' => $viewer->id,
            ],
            'user' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'email' => $subject->email,
                'sponsor_id' => $subject->sponsor_id,
                'commission_balance' => (float) $subject->commission_balance,
                'joined_at' => optional($subject->created_at)->toDateTimeString(),
                'membership' => $subject->membership?->membershipType?->name,
                'last_approved_payment_at' => optional($subject->payments->first()?->reviewed_at)->toDateTimeString(),
                'pending_profits_total' => (float) Profit::query()->where('user_id', $subject->id)->where('state', 'pending')->sum('amount'),
                'paid_profits_total' => (float) Profit::query()->where('user_id', $subject->id)->where('state', 'made')->sum('amount'),
            ],
            'sponsors' => $sponsors->map(fn (array $row) => [
                'level' => $row['level'],
                'id' => $row['user']->id,
                'name' => $row['user']->name,
                'email' => $row['user']->email,
            ])->all(),
            'affiliates' => $affiliates,
        ];
    }

    public function resolveRootUser(): User
    {
        $admin = User::query()
            ->whereColumn('id', 'sponsor_id')
            ->orderBy('id')
            ->first();

        if ($admin instanceof User) {
            return $admin;
        }

        return User::query()->orderBy('id')->firstOrFail();
    }

    protected function isRootUser(User $user): bool
    {
        return (int) $user->id === (int) $user->sponsor_id;
    }

    protected function isDescendantOfViewer(User $viewer, User $subject): bool
    {
        if ((int) $viewer->id === (int) $subject->id) {
            return false;
        }

        $current = $subject;

        for ($guard = 0; $guard < 30; $guard++) {
            $sponsorId = (int) ($current->sponsor_id ?? 0);

            if ($sponsorId <= 0 || $sponsorId === (int) $current->id) {
                return false;
            }

            if ($sponsorId === (int) $viewer->id) {
                return true;
            }

            $current = User::query()->select(['id', 'sponsor_id'])->find($sponsorId);
            if (! $current instanceof User) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param array<int, list<User>> $childrenBySponsor
     * @return array<string, mixed>
     */
    protected function nodeFromUser(User $user, array $childrenBySponsor, int $depth, int $maxDepth): array
    {
        $children = collect($childrenBySponsor[$user->id] ?? [])
            ->filter(fn (User $child) => $child->id !== $child->sponsor_id)
            ->values();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'sponsor_id' => $user->sponsor_id,
            'commission_balance' => (float) $user->commission_balance,
            'joined_at' => optional($user->created_at)->toDateTimeString(),
            'membership' => $user->membership?->membershipType?->name,
            'direct_affiliates_count' => $children->count(),
            'children' => $depth >= $maxDepth
                ? []
                : $children->map(fn (User $child) => $this->nodeFromUser($child, $childrenBySponsor, $depth + 1, $maxDepth))->all(),
        ];
    }
}
