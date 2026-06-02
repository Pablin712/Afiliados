<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PaymentPendingWebhookService
{
    public function send(Payment $payment): void
    {
        $webhookUrl = trim((string) config('affiliates.payment_verifier_webhook_url', ''));
        if ($webhookUrl === '') {
            return;
        }

        $payment->loadMissing(['user:id,name,email', 'transaction.bank:id,name,owner,identification,number,detail']);

        $receiptPublicUrl = URL::temporarySignedRoute(
            'api.public.payments.receipt',
            now()->addHours(6),
            ['payment' => $payment->id]
        );

        $payload = [
            'event' => 'payment.pending',
            'payment_id' => (int) $payment->id,
            'user_id' => (int) $payment->user_id,
            'bank_id' => (int) ($payment->transaction?->bank?->id ?? 0),
            'payment_number' => (string) $payment->number,
            'amount' => (float) $payment->amount,
            'created_at' => optional($payment->created_at)?->toIso8601String(),
            'trace_id' => sprintf('payment-%d-%s', $payment->id, now()->format('Ymd-His')),
            'bank' => [
                'id' => (int) ($payment->transaction?->bank?->id ?? 0),
                'name' => (string) ($payment->transaction?->bank?->name ?? ''),
                'owner' => (string) ($payment->transaction?->bank?->owner ?? ''),
                'identification' => (string) ($payment->transaction?->bank?->identification ?? ''),
                'number' => (string) ($payment->transaction?->bank?->number ?? ''),
                'detail' => (string) ($payment->transaction?->bank?->detail ?? ''),
            ],
            'payment_url' => route('api.admin.payments.pending.show', ['payment' => $payment->id]),
            'receipt_url' => $receiptPublicUrl,
            'receipt_internal_url' => route('api.admin.payments.pending.receipt', ['payment' => $payment->id]),
            'receipt_public_url' => $receiptPublicUrl,
            'approve_url' => route('api.admin.payments.pending.approve', ['payment' => $payment->id]),
            'reject_url' => route('api.admin.payments.pending.reject', ['payment' => $payment->id]),
            // Backward-compatible aliases for existing n8n verifier flows.
            'payment_id' => (int) $payment->id,
            'user_id' => (int) $payment->user_id,
            'bank_id' => (int) ($payment->transaction?->bank?->id ?? 0),
            'banco_nombre' => (string) ($payment->transaction?->bank?->name ?? ''),
            'numcomprobante' => (string) $payment->number,
            'valor' => (float) $payment->amount,
            'recarga_url' => route('api.admin.payments.pending.show', ['payment' => $payment->id]),
            'foto_url' => $receiptPublicUrl,
            'approve_legacy_url' => route('api.v2.payments.n8n.recargas.aprobar', ['payment' => $payment->id]),
            'reject_legacy_url' => route('api.v2.payments.n8n.recargas.rechazar', ['payment' => $payment->id]),
            'banco' => [
                'id' => (int) ($payment->transaction?->bank?->id ?? 0),
                'nombre' => (string) ($payment->transaction?->bank?->name ?? ''),
                'propietario' => (string) ($payment->transaction?->bank?->owner ?? ''),
                'cedula' => (string) ($payment->transaction?->bank?->identification ?? ''),
                'numero' => (string) ($payment->transaction?->bank?->number ?? ''),
                'tipo' => '',
                'detalle' => (string) ($payment->transaction?->bank?->detail ?? ''),
                'foto' => null,
                'monto' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
        ];

        $token = trim((string) config('affiliates.payment_verifier_webhook_token', ''));

        try {
            $request = Http::timeout(10)->acceptJson();

            if ($token !== '') {
                $request = $request->withHeaders([
                    'X-Webhook-Token' => $token,
                ]);
            }

            $response = $request->post($webhookUrl, $payload);

            if (! $response->successful()) {
                Log::warning('Payment verifier webhook returned non-success status.', [
                    'payment_id' => $payment->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Payment verifier webhook request failed.', [
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
