<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentPendingWebhookService
{
    public function send(Payment $payment): void
    {
        $webhookUrl = trim((string) config('affiliates.payment_verifier_webhook_url', ''));
        if ($webhookUrl === '') {
            return;
        }

        $payment->loadMissing(['user:id,name,email', 'transaction.bank:id,name,owner,identification,number,detail']);

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
            'receipt_url' => route('api.admin.payments.pending.receipt', ['payment' => $payment->id]),
            'approve_url' => route('api.admin.payments.pending.approve', ['payment' => $payment->id]),
            'reject_url' => route('api.admin.payments.pending.reject', ['payment' => $payment->id]),
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
