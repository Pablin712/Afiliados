<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $apiBase;

    public function __construct()
    {
        $token = trim((string) config('affiliates.telegram.bot_token', ''));
        $this->apiBase = "https://api.telegram.org/bot{$token}";
    }

    /**
     * Ban a Telegram user from every active, exclusive Telegram channel
     * (i.e. groups free members shouldn't be part of).
     * @return array<string, bool> Channel name => success
     */
    public function banFromAllGroups(int $telegramUserId): array
    {
        $channels = Channel::query()->type(Channel::TYPE_TELEGRAM)->exclusive()->active()->get();
        $results = [];

        foreach ($channels as $channel) {
            if ($channel->chat_id === null || $channel->chat_id === '') {
                continue;
            }

            $results[$channel->name] = $this->banFromGroup($channel->chat_id, $telegramUserId, $channel->bot_token);
        }

        return $results;
    }

    /**
     * @param  string|null  $botToken  Overrides the globally configured bot token
     *                                 (e.g. when banning through a per-channel bot).
     */
    public function banFromGroup(string $groupChatId, int $telegramUserId, ?string $botToken = null): bool
    {
        $apiBase = $botToken !== null && trim($botToken) !== ''
            ? 'https://api.telegram.org/bot'.trim($botToken)
            : $this->apiBase;

        if ($apiBase === 'https://api.telegram.org/bot') {
            Log::warning('Telegram bot token not configured, skipping ban.', [
                'user_id'  => $telegramUserId,
                'group'    => $groupChatId,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(10)->post("{$apiBase}/banChatMember", [
                'chat_id' => $groupChatId,
                'user_id' => $telegramUserId,
            ]);

            $success = $response->successful();

            Log::info('Telegram ban from group.', [
                'group_chat_id'    => $groupChatId,
                'telegram_user_id' => $telegramUserId,
                'http_status'      => $response->status(),
                'success'          => $success,
            ]);

            if (! $success) {
                Log::warning('Telegram banChatMember returned non-success.', [
                    'group_chat_id' => $groupChatId,
                    'http_status'   => $response->status(),
                    'body'          => $response->body(),
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::warning('Telegram banChatMember request failed.', [
                'group_chat_id'    => $groupChatId,
                'telegram_user_id' => $telegramUserId,
                'error'            => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  string|null  $botToken  Overrides the globally configured bot token
     *                                 (e.g. when sending through a per-channel bot).
     */
    public function sendMessage(int|string $chatId, string $text, ?string $botToken = null): bool
    {
        $apiBase = $botToken !== null && trim($botToken) !== ''
            ? 'https://api.telegram.org/bot'.trim($botToken)
            : $this->apiBase;

        if ($apiBase === 'https://api.telegram.org/bot') {
            Log::warning('Telegram bot token not configured, skipping sendMessage.');

            return false;
        }

        try {
            $response = Http::timeout(10)->post("{$apiBase}/sendMessage", [
                'chat_id' => (string) $chatId,
                'text'    => $text,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Telegram sendMessage failed.', [
                'chat_id' => (string) $chatId,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }
}
