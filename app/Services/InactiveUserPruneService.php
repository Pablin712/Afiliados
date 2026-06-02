<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class InactiveUserPruneService
{
    /**
     * @return array{threshold:string,total_candidates:int,deleted:int,dry_run:bool,user_ids:list<int>}
     */
    public function prune(int $months = 3, bool $dryRun = false, ?int $limit = null): array
    {
        $threshold = now()->subMonths($months);
        $thresholdTs = $threshold->timestamp;

        $candidates = User::query()
            ->role('user')
            ->select(['users.id', 'users.sponsor_id'])
            ->selectRaw('MAX(sessions.last_activity) as last_activity_ts')
            ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.sponsor_id')
            ->havingRaw('COALESCE(MAX(sessions.last_activity), 0) < ?', [$thresholdTs])
            ->havingRaw('UNIX_TIMESTAMP(MIN(users.created_at)) < ?', [$thresholdTs])
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->get();

        $deleted = 0;
        $userIds = [];

        foreach ($candidates as $candidate) {
            $userId = (int) $candidate->id;
            $userIds[] = $userId;

            if ($dryRun) {
                continue;
            }

            $user = User::query()->find($userId);
            if (! $user instanceof User) {
                continue;
            }

            $fallbackSponsorId = $this->resolveFallbackSponsorId($user);

            DB::transaction(function () use ($user, $fallbackSponsorId): void {
                User::query()
                    ->where('sponsor_id', $user->id)
                    ->update(['sponsor_id' => $fallbackSponsorId]);

                $user->delete();
            });

            $deleted++;
        }

        return [
            'threshold' => $threshold->toIso8601String(),
            'total_candidates' => $candidates->count(),
            'deleted' => $deleted,
            'dry_run' => $dryRun,
            'user_ids' => $userIds,
        ];
    }

    private function resolveFallbackSponsorId(User $user): int
    {
        $sponsorId = (int) ($user->sponsor_id ?? 0);

        if ($sponsorId > 0 && $sponsorId !== (int) $user->id) {
            return $sponsorId;
        }

        return (int) (User::query()->where('id', '!=', $user->id)->whereKey(1)->value('id')
            ?? User::query()->where('id', '!=', $user->id)->orderBy('id')->value('id')
            ?? $user->id);
    }
}
