<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;

class MembershipReminderService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function usersWithMembershipExpiredOnDate(CarbonInterface $date, int $limit = 500): array
    {
        $users = User::query()
            ->role('user')
            ->with([
                'membership.membershipType:id,name',
                'sponsor:id,name,email',
            ])
            ->whereHas('membership', function ($query) use ($date): void {
                $query
                    ->whereNotNull('expires_at')
                    ->whereDate('expires_at', $date);
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return $users->map(function (User $user) use ($date): array {
            $membership = $user->membership;

            return [
                'user_id' => (int) $user->id,
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'phone' => (string) ($user->phone ?? ''),
                'affiliate_code' => (string) ($user->affiliate_code ?? ''),
                'membership' => [
                    'type' => strtolower((string) ($membership?->membershipType?->name ?? 'unknown')),
                    'status' => (string) ($membership?->status ?? ''),
                    'started_at' => optional($membership?->started_at)?->toIso8601String(),
                    'expires_at' => optional($membership?->expires_at)?->toIso8601String(),
                    'expired_date' => $date->toDateString(),
                ],
                'sponsor' => [
                    'id' => (int) ($user->sponsor?->id ?? 0),
                    'name' => (string) ($user->sponsor?->name ?? ''),
                    'email' => (string) ($user->sponsor?->email ?? ''),
                ],
                'reminder' => [
                    'event' => 'membership.expired',
                    'message_es' => 'Tu membresía venció hoy. Por favor reactiva para mantener tus beneficios.',
                    'message_en' => 'Your membership expired today. Please reactivate to keep your benefits.',
                ],
            ];
        })->values()->all();
    }
}
