<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappGroupService
{
    /**
     * Remove one or more participants from every active, exclusive WhatsApp
     * group (i.e. groups free members shouldn't be part of).
     *
     * @param  string[]  $phones  Raw phone numbers (any common format)
     * @return array{removed: int, phones: string[], success: bool, channels: array<string, bool>}
     */
    public function removeParticipants(array $phones): array
    {
        $normalized = array_values(array_filter(
            array_map(fn (string $p): string => $this->normalizePhone($p), $phones)
        ));

        if ($normalized === []) {
            Log::warning('WhatsApp group: no valid phone numbers after normalization.', [
                'raw_phones' => $phones,
            ]);

            return ['removed' => 0, 'phones' => [], 'success' => false, 'channels' => []];
        }

        $channels = Channel::query()->type(Channel::TYPE_WHATSAPP)->exclusive()->active()->get();

        if ($channels->isEmpty()) {
            Log::warning('WhatsApp group: no active exclusive channel configured, skipping participant removal.', [
                'phones_count' => count($normalized),
            ]);

            return ['removed' => 0, 'phones' => $normalized, 'success' => false, 'channels' => []];
        }

        $channelResults = [];
        $anySuccess = false;

        foreach ($channels as $channel) {
            $success = $this->removeParticipantsFromChannel($channel, $normalized);
            $channelResults[$channel->name] = $success;

            if ($success) {
                $anySuccess = true;
            }
        }

        return [
            'removed'  => $anySuccess ? count($normalized) : 0,
            'phones'   => $normalized,
            'success'  => $anySuccess,
            'channels' => $channelResults,
        ];
    }

    public function removeParticipant(string $phone): bool
    {
        return $this->removeParticipants([$phone])['success'];
    }

    /**
     * @param  string[]  $normalizedPhones
     */
    private function removeParticipantsFromChannel(Channel $channel, array $normalizedPhones): bool
    {
        $serverUrl = rtrim(trim((string) $channel->server_url), '/');
        $instanceName = trim((string) $channel->instance_name);
        $groupJid = trim((string) $channel->chat_id);
        $apikey = trim((string) $channel->api_key);

        if ($serverUrl === '' || $instanceName === '' || $groupJid === '' || $apikey === '') {
            Log::warning('WhatsApp channel config incomplete, skipping participant removal.', [
                'channel_id' => $channel->id,
                'channel'    => $channel->name,
            ]);

            return false;
        }

        $url = "{$serverUrl}/group/updateParticipant/{$instanceName}";

        try {
            $response = Http::timeout(15)
                ->withHeaders(['apikey' => $apikey])
                ->post($url.'?groupJid='.urlencode($groupJid), [
                    'action'       => 'remove',
                    'participants' => $normalizedPhones,
                ]);

            $success = $response->successful();

            Log::info('WhatsApp group remove participants.', [
                'channel'      => $channel->name,
                'participants' => $normalizedPhones,
                'status'       => $response->status(),
                'success'      => $success,
            ]);

            if (! $success) {
                Log::warning('WhatsApp group API returned non-success status.', [
                    'channel' => $channel->name,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp group remove participants failed.', [
                'channel' => $channel->name,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send a text message via Evolution API to a phone number or group JID.
     */
    public function sendText(string $serverUrl, string $instanceName, string $apiKey, string $number, string $text): bool
    {
        $serverUrl = rtrim(trim($serverUrl), '/');
        $instanceName = trim($instanceName);
        $apiKey = trim($apiKey);
        $number = trim($number);

        if ($serverUrl === '' || $instanceName === '' || $apiKey === '' || $number === '') {
            Log::warning('WhatsApp sendText: incomplete channel config, skipping.', [
                'instance' => $instanceName,
                'number'   => $number,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['apiKey' => $apiKey])
                ->post("{$serverUrl}/message/sendText/{$instanceName}", [
                    'number' => $number,
                    'text'   => $text,
                ]);

            $success = $response->successful();

            if (! $success) {
                Log::warning('WhatsApp sendText returned non-success status.', [
                    'instance' => $instanceName,
                    'number'   => $number,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp sendText request failed.', [
                'instance' => $instanceName,
                'number'   => $number,
                'error'    => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $hasPlus = str_starts_with($phone, '+');
        $digits  = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        // Already has explicit international prefix
        if ($hasPlus) {
            return $digits;
        }

        // 00-prefixed international format (e.g. 00593...)
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        // Treat as already-correct international digits (e.g. 593xxxxxxx)
        if (preg_match('/^[1-9]\d{7,14}$/', $digits)) {
            return $digits;
        }

        return '';
    }
}
