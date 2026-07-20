<?php

namespace App\Services;

use App\Models\MessageTemplate;
use App\Models\User;
use Carbon\CarbonInterface;

class MembershipReminderService
{
    public function __construct(
        private readonly MembershipFreeRenewalService $membershipFreeRenewalService,
    ) {
    }

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

        // Users who already earned a free renewal for this period get auto-renewed by
        // memberships:downgrade-expired instead of being downgraded, so they shouldn't
        // receive a "reactivate to keep your benefits" reminder.
        $users = $users->reject(function (User $user): bool {
            $membership = $user->membership;

            return $membership !== null && $this->membershipFreeRenewalService->qualifies($membership);
        })->values();

        $reminderMessageEs = MessageTemplate::bodyFor(
            'membership_expiring',
            'Tu membresía vence mañana. Por favor reactiva para mantener tus beneficios.'
        );

        return $users->map(function (User $user) use ($date, $reminderMessageEs): array {
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
                    'expires_on' => $date->toDateString(),
                    'expired_date' => $date->toDateString(),
                ],
                'sponsor' => [
                    'id' => (int) ($user->sponsor?->id ?? 0),
                    'name' => (string) ($user->sponsor?->name ?? ''),
                    'email' => (string) ($user->sponsor?->email ?? ''),
                ],
                'reminder' => [
                    'event' => 'membership.expiring_soon',
                    'message_es' => $reminderMessageEs,
                    'message_en' => 'Your membership expires tomorrow. Please reactivate to keep your benefits.',
                ],
            ];
        })->values()->all();
    }
}
