<?php

namespace App\Services;

use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistrationWhatsappService
{
    public function send(User $user): void
    {
        $fallback = 'Bienvenido/a {name} a AET Trader Academy.'
            ."\n\nDesde este momento estare acompanandote en tu proceso dentro del sistema."
            ."\n\nSi ya estas usando la version gratuita, entra ahora a nuestro canal de Telegram:"
            ."\nhttps://t.me/aetsas";

        $template = MessageTemplate::where('key', 'bienvenida')->first();
        $welcomeMessage = $this->renderTemplate($template?->body ?? $fallback, $user);

        $payload = $this->buildStandardPayload(
            $user,
            tipo: 'bienvenida',
            event: 'user.welcome',
            mensajeEs: $welcomeMessage,
            mensajeEn: 'Welcome to AET Trader Academy. Check your onboarding instructions in the message_es content.'
        );

        $this->sendPayload($user, $payload);
    }

    public function sendPostPago(User $user): void
    {
        $fallback = 'Bienvenido/a a AET Trader Academy.'
            ."\n\nAcceso a la comunidad:"
            ."\nhttps://t.me/+tv9B-1V8eWdhMjIx";

        $template = MessageTemplate::where('key', 'post_pago')->first();
        $message = $this->renderTemplate($template?->body ?? $fallback, $user);

        $payload = $this->buildStandardPayload(
            $user,
            tipo: 'post_pago',
            event: 'user.post_pago',
            mensajeEs: $message,
            mensajeEn: 'Your payment was approved. Check your premium community access links in message_es.'
        );

        $this->sendPayload($user, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStandardPayload(
        User $user,
        string $tipo,
        string $event,
        string $mensajeEs,
        string $mensajeEn
    ): array {
        $rawPhone = (string) ($user->phone ?? '');
        $normalizedPhone = $this->normalizePhoneToE164($rawPhone);

        return [
            'tipo' => $tipo,
            'event' => $event,
            'user_id' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'phone' => $normalizedPhone,
            'phone_raw' => $rawPhone,
            'mensaje' => $mensajeEs,
            'mensaje_es' => $mensajeEs,
            'mensaje_en' => $mensajeEn,
            'message_es' => $mensajeEs,
            'message_en' => $mensajeEn,
            'usuario' => [
                'id' => (int) $user->id,
                'nombre' => (string) $user->name,
                'email' => (string) $user->email,
                'phone' => $normalizedPhone,
                'telefono' => $normalizedPhone,
            ],
            'created_at' => optional($user->created_at)?->toIso8601String(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function sendPayload(User $user, array $payload): void
    {
        $webhookUrl = trim((string) config('affiliates.registration_whatsapp_webhook_url', ''));
        if ($webhookUrl === '') {
            Log::warning('Registration WhatsApp webhook URL is empty, skipping dispatch.', [
                'user_id' => (int) $user->id,
                'tipo' => (string) ($payload['tipo'] ?? ''),
            ]);
            return;
        }

        //$token = trim((string) config('affiliates.registration_whatsapp_webhook_token', ''));

        try {
            $request = Http::timeout(10)->acceptJson();

            /*if ($token !== '') {
                $request = $request->withHeaders([
                    'X-Webhook-Token' => $token,
                ]);
            }*/

            Log::info('Dispatching registration WhatsApp webhook.', [
                'user_id' => (int) $user->id,
                'tipo' => (string) ($payload['tipo'] ?? ''),
                'url' => $webhookUrl,
            ]);

            $response = $request->post($webhookUrl, $payload);

            Log::info('Registration WhatsApp webhook response received.', [
                'user_id' => (int) $user->id,
                'tipo' => (string) ($payload['tipo'] ?? ''),
                'status' => $response->status(),
            ]);

            if (! $response->successful()) {
                Log::warning('Registration WhatsApp webhook returned non-success status.', [
                    'user_id' => $user->id,
                    'tipo' => (string) ($payload['tipo'] ?? ''),
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Registration WhatsApp webhook request failed.', [
                'user_id' => $user->id,
                'tipo' => (string) ($payload['tipo'] ?? ''),
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function renderTemplate(string $body, User $user): string
    {
        return str_replace(
            ['{name}', '{email}', '{phone}'],
            [(string) $user->name, (string) $user->email, (string) ($user->phone ?? '')],
            $body
        );
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

        if (preg_match('/^[1-9]\d{7,14}$/', $digits)) {
            return '+'.$digits;
        }

        return '';
    }
}
