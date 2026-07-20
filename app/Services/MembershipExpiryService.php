<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\MembershipType;

class MembershipExpiryService
{
    public function __construct(
        private readonly WhatsappGroupService $whatsappGroupService,
        private readonly TelegramService $telegramService,
        private readonly MembershipFreeRenewalService $membershipFreeRenewalService,
        private readonly RegistrationWhatsappService $registrationWhatsappService,
    ) {
    }

    /**
     * Downgrade active memberships past their expiry date to free (and remove them from
     * WhatsApp/Telegram groups), unless they qualify for a free renewal — in which case the
     * membership opens a new one-month period instead of being downgraded, at no charge.
     *
     * @return array<string, mixed>
     */
    public function processExpired(bool $dryRun = false): array
    {
        $freeMembershipType = MembershipType::query()
            ->whereRaw('LOWER(name) = ?', ['free'])
            ->first();

        $expired = Membership::query()
            ->with('user')
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            return [
                'processed' => 0,
                'downgraded' => 0,
                'free_renewals' => 0,
                'dry_run' => $dryRun,
                'downgraded_user_ids' => [],
                'free_renewal_user_ids' => [],
                'whatsapp_group_removed' => 0,
                'telegram_banned' => 0,
            ];
        }

        $phonesToRemove = [];
        $telegramIdsToBan = [];
        $downgradedUserIds = [];
        $freeRenewalUserIds = [];

        foreach ($expired as $membership) {
            if ($this->membershipFreeRenewalService->qualifies($membership)) {
                $freeRenewalUserIds[] = (int) $membership->user_id;

                if (! $dryRun) {
                    $newPeriodStart = $membership->expires_at->copy();
                    $membership->started_at = $newPeriodStart;
                    $membership->expires_at = $newPeriodStart->copy()->addMonth();
                    $membership->save();

                    if ($membership->user !== null) {
                        $this->registrationWhatsappService->sendFreeRenewal($membership->user);
                    }
                }

                continue;
            }

            $downgradedUserIds[] = (int) $membership->user_id;

            $phone = trim((string) ($membership->user?->phone ?? ''));
            if ($phone !== '') {
                $phonesToRemove[] = $phone;
            }

            $telegramChatId = $membership->user?->telegram_chat_id;
            if ($telegramChatId !== null) {
                $telegramIdsToBan[] = (int) $telegramChatId;
            }

            if (! $dryRun) {
                $membership->status = 'free';

                if ($freeMembershipType !== null) {
                    $membership->membership_type_id = $freeMembershipType->id;
                }

                $membership->save();
            }
        }

        $whatsappRemoved = 0;
        $telegramBanned = 0;

        if (! $dryRun) {
            if ($phonesToRemove !== []) {
                $whatsappResult = $this->whatsappGroupService->removeParticipants($phonesToRemove);
                $whatsappRemoved = (int) ($whatsappResult['removed'] ?? 0);
            }

            foreach ($telegramIdsToBan as $telegramUserId) {
                $groupResults = $this->telegramService->banFromAllGroups($telegramUserId);
                if (in_array(true, array_values($groupResults), true)) {
                    $telegramBanned++;
                }
            }
        }

        return [
            'processed' => $expired->count(),
            'downgraded' => count($downgradedUserIds),
            'free_renewals' => count($freeRenewalUserIds),
            'dry_run' => $dryRun,
            'downgraded_user_ids' => $downgradedUserIds,
            'free_renewal_user_ids' => $freeRenewalUserIds,
            'whatsapp_group_removed' => $whatsappRemoved,
            'telegram_banned' => $telegramBanned,
        ];
    }
}
