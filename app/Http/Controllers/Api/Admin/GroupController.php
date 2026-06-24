<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramService;
use App\Services\WhatsappGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(
        private readonly WhatsappGroupService $whatsappGroupService,
        private readonly TelegramService $telegramService,
    ) {}

    /**
     * Remove all current free members from the WhatsApp group.
     * Useful for the initial cleanup or emergency runs via n8n.
     */
    public function removeFreeMembersWhatsapp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $dryRun = (bool) ($validated['dry_run'] ?? false);

        $users = User::query()
            ->whereHas('membership', fn ($q) => $q->where('status', 'free'))
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get(['id', 'name', 'phone']);

        $phones = $users->pluck('phone')->all();

        $result = ['removed' => 0, 'phones' => $phones, 'success' => false];

        if (! $dryRun && $phones !== []) {
            $result = $this->whatsappGroupService->removeParticipants($phones);
        }

        return response()->json([
            'message' => $dryRun
                ? 'Dry run: no changes made. Showing candidates for WhatsApp group removal.'
                : 'Free members removal from WhatsApp group processed.',
            'meta' => [
                'dry_run'    => $dryRun,
                'candidates' => count($phones),
                'removed'    => $result['removed'],
                'success'    => $result['success'],
            ],
            'data' => [
                'users' => $users->map(fn (User $u) => [
                    'id'    => (int) $u->id,
                    'name'  => (string) $u->name,
                    'phone' => (string) ($u->phone ?? ''),
                ])->values(),
                'phones_normalized' => $result['phones'],
            ],
        ]);
    }

    /**
     * Ban all current free members from all Telegram groups.
     * Only affects users who have a registered telegram_chat_id.
     */
    public function removeFreeMembersTelegram(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $dryRun = (bool) ($validated['dry_run'] ?? false);

        $users = User::query()
            ->whereHas('membership', fn ($q) => $q->where('status', 'free'))
            ->whereNotNull('telegram_chat_id')
            ->get(['id', 'name', 'telegram_chat_id']);

        $banned = 0;
        $results = [];

        if (! $dryRun) {
            foreach ($users as $user) {
                $groupResults = $this->telegramService->banFromAllGroups((int) $user->telegram_chat_id);
                $results[(int) $user->id] = $groupResults;
                if (in_array(true, array_values($groupResults), true)) {
                    $banned++;
                }
            }
        }

        return response()->json([
            'message' => $dryRun
                ? 'Dry run: no changes made. Showing candidates for Telegram group ban.'
                : 'Free members Telegram ban processed.',
            'meta' => [
                'dry_run'    => $dryRun,
                'candidates' => $users->count(),
                'banned'     => $banned,
            ],
            'data' => [
                'users' => $users->map(fn (User $u) => [
                    'id'               => (int) $u->id,
                    'name'             => (string) $u->name,
                    'telegram_chat_id' => $u->telegram_chat_id,
                ])->values(),
                'results' => $results,
            ],
        ]);
    }
}
