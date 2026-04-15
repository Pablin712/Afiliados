<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistrationWhatsappService
{
    public function send(User $user): void
    {
        $webhookUrl = trim((string) config('affiliates.registration_whatsapp_webhook_url', ''));
        if ($webhookUrl === '') {
            return;
        }

        $rawPhone = (string) ($user->phone ?? '');
        $normalizedPhone = $this->normalizePhoneToE164($rawPhone);

        $payload = [
            'tipo' => 'registro_exitoso',
            'event' => 'user.registered',
            'user_id' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'phone' => $normalizedPhone,
            'phone_raw' => $rawPhone,
            'message_es' => '✅ Registro exitoso en Afiliados para '.$user->name,
            'message_en' => '✅ Successful registration in Afiliados for '.$user->name,
            'created_at' => optional($user->created_at)?->toIso8601String(),
        ];

        $token = trim((string) config('affiliates.registration_whatsapp_webhook_token', ''));

        try {
            $request = Http::timeout(10)->acceptJson();

            if ($token !== '') {
                $request = $request->withHeaders([
                    'X-Webhook-Token' => $token,
                ]);
            }

            $response = $request->post($webhookUrl, $payload);

            if (! $response->successful()) {
                Log::warning('Registration WhatsApp webhook returned non-success status.', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Registration WhatsApp webhook request failed.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizePhoneToE164(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if ($hasPlus) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+'.substr($digits, 2);
        }

        $defaultCountryCode = preg_replace(
            '/\D+/',
            '',
            (string) config('affiliates.registration_whatsapp_default_country_code', '593')
        ) ?? '';

        if ($defaultCountryCode !== '' && str_starts_with($digits, '0')) {
            return '+'.$defaultCountryCode.ltrim($digits, '0');
        }

        if ($defaultCountryCode !== '' && ! str_starts_with($digits, $defaultCountryCode) && strlen($digits) <= 10) {
            return '+'.$defaultCountryCode.$digits;
        }

        return '+'.$digits;
    }
}
