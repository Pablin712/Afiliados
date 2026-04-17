<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistrationWhatsappService
{
    public function send(User $user): void
    {
        $welcomeMessage = "¡Bienvenido/a {$user->name} a AET Trader Academy! 👨🏻‍💻\n\n"
            ."Desde este momento estaré acompañándote en tu proceso dentro del sistema.\n"
            ."Soy Donna - asistente de AET Trader Academy.\n\n"
            ."Si ya estás usando la versión gratuita, el siguiente paso es simple:\n"
            ."entra ahora a nuestro canal de Telegram.\n\n"
            ."https://t.me/aetsas\n\n"
            ."Ahí vas a encontrar los enlaces gratuitos y todo el contenido para que empieces correctamente.\n\n"
            ."Además, te recomendamos crearte tu cuenta a través de los enlaces que tienes en la página principal de AET, para que puedas operar y aprovechar correctamente todas las herramientas.\n\n"
            ."Ahora, si realmente quieres resultados y no solo mirar, el plan premium de $147 te desbloquea todo:\n"
            ."escáner, señales y clases en vivo.\n\n"
            ."No te quedes a medias. Avanza.\n\n"
            ."Te recomiendo analizar en\n"
            ."https://tradingview.deriv.com/\n\n"
            ."Y trabajar con cuentas standar mt5 que podrás aperturar dentro de los links de nuestros brokers asociados.";

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
        $message = "¡Bienvenido/a a AET Trader Academy! 👨🏻‍💻\n\n"
            ."Desde este momento estaré acompañándote en tu proceso dentro del sistema.\n"
            ."Soy Donna - asistente de AET Trader Academy.\n\n"
            ."Acceso a la comunidad\n\n"
            ."Canal oficial\n"
            ."https://t.me/+tv9B-1V8eWdhMjIx\n\n"
            ."Señales VIP\n"
            ."https://t.me/+AoQTrGdNxhNlMWFh\n"
            ."https://t.me/+tJuQIHiKP0JkYzYx\n\n"
            ."Grupo Premium\n"
            ."https://t.me/+jVdPcBcKNFAyYzZh\n\n\n"
            ."Si también quieres generar ingresos recomendando el sistema, responde:\n\n"
            ."“Quiero estar en el sistema de referidos”\n\n"
            ."y agendamos un Zoom para explicarte cómo funciona.\n\n"
            ."A partir de ahora estaré pendiente para ayudarte en tu proceso dentro de AET TRADER ACADEMY";

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
