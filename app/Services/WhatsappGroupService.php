<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappGroupService
{
    /**
     * Remove one or more participants from the WhatsApp group.
     *
     * @param  string[]  $phones  Raw phone numbers (any common format)
     * @return array{removed: int, phones: string[], success: bool}
     */
    public function removeParticipants(array $phones): array
    {
        $url      = trim((string) config('affiliates.whatsapp_group.url', ''));
        $groupJid = trim((string) config('affiliates.whatsapp_group.group_jid', ''));
        $apikey   = trim((string) config('affiliates.whatsapp_group.apikey', ''));

        if ($url === '' || $groupJid === '' || $apikey === '') {
            Log::warning('WhatsApp group config incomplete, skipping participant removal.', [
                'phones_count' => count($phones),
            ]);

            return ['removed' => 0, 'phones' => [], 'success' => false];
        }

        $normalized = array_values(array_filter(
            array_map(fn (string $p): string => $this->normalizePhone($p), $phones)
        ));

        if ($normalized === []) {
            Log::warning('WhatsApp group: no valid phone numbers after normalization.', [
                'raw_phones' => $phones,
            ]);

            return ['removed' => 0, 'phones' => [], 'success' => false];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['apikey' => $apikey])
                ->post($url.'?groupJid='.urlencode($groupJid), [
                    'action'       => 'remove',
                    'participants' => $normalized,
                ]);

            $success = $response->successful();

            Log::info('WhatsApp group remove participants.', [
                'participants' => $normalized,
                'status'       => $response->status(),
                'success'      => $success,
            ]);

            if (! $success) {
                Log::warning('WhatsApp group API returned non-success status.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }

            return [
                'removed' => $success ? count($normalized) : 0,
                'phones'  => $normalized,
                'success' => $success,
            ];
        } catch (\Throwable $e) {
            Log::warning('WhatsApp group remove participants failed.', [
                'error'        => $e->getMessage(),
                'participants' => $normalized,
            ]);

            return ['removed' => 0, 'phones' => $normalized, 'success' => false];
        }
    }

    public function removeParticipant(string $phone): bool
    {
        return $this->removeParticipants([$phone])['success'];
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
