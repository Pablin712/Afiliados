<?php

namespace App\Services;

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
