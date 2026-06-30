<?php

namespace App\Console\Commands;

use App\Models\Membership;
use App\Models\MembershipType;
use App\Services\TelegramService;
use App\Services\WhatsappGroupService;
use Illuminate\Console\Command;

class DowngradeExpiredMembershipsCommand extends Command
{
    protected $signature = 'memberships:downgrade-expired';

    protected $description = 'Downgrade active memberships past their expiry date to free and remove from groups';

    public function __construct(
        private readonly WhatsappGroupService $whatsappGroupService,
        private readonly TelegramService $telegramService,
    ) {
        parent::__construct();
    }

    public function handle(): int
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
            $this->info('No expired memberships found.');

            return self::SUCCESS;
        }

        $phonesToRemove = [];
        $telegramIdsToBan = [];

        foreach ($expired as $membership) {
            $membership->status = 'free';

            if ($freeMembershipType !== null) {
                $membership->membership_type_id = $freeMembershipType->id;
            }

            $membership->save();

            $phone = trim((string) ($membership->user?->phone ?? ''));
            if ($phone !== '') {
                $phonesToRemove[] = $phone;
            }

            $telegramChatId = $membership->user?->telegram_chat_id;
            if ($telegramChatId !== null) {
                $telegramIdsToBan[] = (int) $telegramChatId;
            }
        }

        if ($phonesToRemove !== []) {
            $this->whatsappGroupService->removeParticipants($phonesToRemove);
        }

        foreach ($telegramIdsToBan as $telegramUserId) {
            $this->telegramService->banFromAllGroups($telegramUserId);
        }

        $this->info("Downgraded {$expired->count()} expired membership(s) to free.");

        return self::SUCCESS;
    }
}
