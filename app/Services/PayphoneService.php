<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayphoneService
{
    private const BASE_URL = 'https://pay.payphonetodoesposible.com/api/button';

    public function prepare(array $payload): array
    {
        $response = Http::withToken($this->token())
            ->timeout(15)
            ->post(self::BASE_URL . '/Prepare', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Payphone Prepare falló: ' . $response->body());
        }

        $data = $response->json();

        if (empty($data['paymentId'])) {
            throw new RuntimeException('Payphone no devolvió paymentId: ' . $response->body());
        }

        return $data;
    }

    public function confirm(string $id, string $clientTransactionId): array
    {
        $response = Http::withToken($this->token())
            ->timeout(15)
            ->post(self::BASE_URL . '/Confirm', [
                'id'                  => $id,
                'clientTransactionId' => $clientTransactionId,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Payphone Confirm falló: ' . $response->body());
        }

        return $response->json();
    }

    private function token(): string
    {
        return (string) config('services.payphone.token', '');
    }
}
