<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Profit;
use App\Models\User;
use Illuminate\Support\Str;

class RankBonusService
{
    public function grantForCurrentMembership(User $user, string $previousTypeName, string $previousStatus): void
    {
        if ($user->hasRole('admin')) {
            return;
        }

        $user->loadMissing('membership.membershipType');

        $membership = $user->membership;
        if ($membership === null || (string) $membership->status !== 'active') {
            return;
        }

        $currentTypeName = $this->normalizeTypeName((string) ($membership->membershipType?->name ?? ''));
        $currentRankIndex = $this->resolveRankIndexByTypeName($currentTypeName);

        if ($currentRankIndex < $this->minimumBonusRankIndex()) {
            return;
        }

        $previousTypeName = $this->normalizeTypeName($previousTypeName);
        $previousRankIndex = $this->resolveRankIndexByTypeName($previousTypeName);
        $previousStatus = Str::lower($previousStatus);
        $periodMonth = now()->startOfMonth()->toDateString();
        $currentMonthStart = now()->startOfMonth();

        if ($currentRankIndex > $previousRankIndex) {
            $amount = round((float) ($membership->membershipType?->profit ?? 0), 2);

            if ($amount <= 0 || $this->hasPromotionBonus($user->id, $currentTypeName)) {
                return;
            }

            $this->createBonusProfit($user, $currentTypeName, 'promotion', $amount, $periodMonth);

            return;
        }

        if ($previousStatus !== 'active' || $currentRankIndex !== $previousRankIndex) {
            return;
        }

        if (! $this->membershipPredatesCurrentMonth($membership->updated_at, $currentMonthStart)) {
            return;
        }

        // During the initial 2-month activation, only one maintenance bonus is allowed for
        // the whole period (not one per calendar month). Subsequent 1-month renewals use
        // the regular per-period check.
        if ($this->isInitialActivationPeriod($membership)) {
            if ($this->hasAnyMaintenanceBonusSince($user->id, $currentTypeName, $membership->started_at)) {
                return;
            }
        } elseif ($this->hasAnyRankBonusInPeriod($user->id, $currentTypeName, $periodMonth)) {
            return;
        }

        $amount = round((float) config("affiliates.rank_maintenance_bonuses.{$currentTypeName}", 0), 2);
        if ($amount <= 0) {
            return;
        }

        $this->createBonusProfit($user, $currentTypeName, 'maintenance', $amount, $periodMonth);
    }

    private function createBonusProfit(User $user, string $rankName, string $kind, float $amount, string $periodMonth): void
    {
        $defaultBank = $user->defaultUserBank()->first();
        $label = $kind === 'promotion'
            ? sprintf('Pending rank bonus for reaching %s.', $rankName)
            : sprintf('Pending monthly maintenance bonus for remaining at %s.', $rankName);

        Profit::query()->create([
            'user_id' => $user->id,
            'user_bank_id' => $defaultBank?->id,
            'period_month' => $periodMonth,
            'amount' => $amount,
            'state' => 'pending',
            'detail' => sprintf('rank_bonus|%s|%s|%s', $kind, $rankName, $label),
        ]);

        $user->increment('commission_balance', $amount);
    }

    private function hasPromotionBonus(int $userId, string $rankName): bool
    {
        return Profit::query()
            ->where('user_id', $userId)
            ->where('detail', 'like', sprintf('rank_bonus|promotion|%s|%%', $rankName))
            ->exists();
    }

    private function hasAnyRankBonusInPeriod(int $userId, string $rankName, string $periodMonth): bool
    {
        return Profit::query()
            ->where('user_id', $userId)
            ->whereDate('period_month', $periodMonth)
            ->where('detail', 'like', sprintf('rank_bonus|%%|%s|%%', $rankName))
            ->exists();
    }

    private function hasAnyMaintenanceBonusSince(int $userId, string $rankName, mixed $since): bool
    {
        return Profit::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->where('detail', 'like', sprintf('rank_bonus|maintenance|%s|%%', $rankName))
            ->exists();
    }

    private function isInitialActivationPeriod(Membership $membership): bool
    {
        if ($membership->started_at === null || $membership->expires_at === null) {
            return false;
        }

        // Initial activation grants 2 months; renewals grant 1 month (~28-31 days).
        // A period longer than 45 days can only be the initial 2-month window.
        return $membership->started_at->diffInDays($membership->expires_at) > 45;
    }

    private function normalizeTypeName(string $typeName): string
    {
        $normalized = Str::lower($typeName);

        return $normalized === 'proffesional' ? 'professional' : $normalized;
    }

    private function minimumBonusRankIndex(): int
    {
        return 3;
    }

    private function membershipPredatesCurrentMonth(mixed $updatedAt, \Illuminate\Support\Carbon $currentMonthStart): bool
    {
        if ($updatedAt === null) {
            return false;
        }

        return $updatedAt->copy()->lt($currentMonthStart);
    }

    private function resolveRankIndexByTypeName(string $typeName): int
    {
        return match ($this->normalizeTypeName($typeName)) {
            'beginner' => 1,
            'constructor' => 2,
            'explorer' => 3,
            'professional' => 4,
            'elite' => 5,
            'master' => 6,
            'legend' => 7,
            default => 0,
        };
    }
}
