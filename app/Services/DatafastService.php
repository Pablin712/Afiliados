<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DatafastService
{
    private string $baseUrl;
    private string $entityId;
    private string $bearerToken;

    public function __construct()
    {
        $this->baseUrl     = rtrim((string) config('affiliates.datafast.base_url', ''), '/');
        $this->entityId    = (string) config('affiliates.datafast.entity_id', '');
        $this->bearerToken = (string) config('affiliates.datafast.bearer_token', '');
    }

    /**
     * Request a checkoutId from the Datafast API.
     *
     * Returns the checkoutId string on success.
     *
     * @throws RuntimeException when the API call fails or the response is invalid.
     */
    public function initiateCheckout(float $amount, User $user, string $shopperResultUrl, string $merchantTransactionId): string
    {
        $ivaRate = (float) config('affiliates.datafast.iva_rate', 0.15);

        [$base0, $baseImp, $iva] = $this->splitTaxAmounts($amount, $ivaRate);

        $testMode     = config('affiliates.datafast.test_mode');
        $isFase2      = ($testMode === 'EXTERNAL');
        $shopperMid   = (string) config('affiliates.datafast.shopper_mid', '');
        $commerceName = (string) config('affiliates.datafast.commerce_name', '');

        // Fase 1 shared test entity only accepts basic params (SHOPPER_* are rejected).
        // Fase 2 requires testMode=EXTERNAL + SHOPPER_* params.
        // Production requires SHOPPER_* params (no testMode).
        // shopperResultUrl must NOT be sent — the widget uses the form's action attribute.
        $useShopper = $isFase2 || (! $isFase2 && $shopperMid !== '' && app()->isProduction());

        $payload = [
            'entityId'              => $this->entityId,
            'amount'                => number_format($amount, 2, '.', ''),
            'currency'              => (string) config('affiliates.datafast.currency', 'USD'),
            'paymentType'           => (string) config('affiliates.datafast.payment_type', 'DB'),
            'merchantTransactionId' => $merchantTransactionId,
            'customer.givenName'    => $this->extractGivenName($user->name),
            'customer.surname'      => $this->extractSurname($user->name),
            'customer.email'        => (string) $user->email,
            'customer.phone'        => (string) ($user->phone ?? ''),
        ];

        if ($isFase2) {
            $payload['testMode'] = 'EXTERNAL';
        }

        if ($useShopper) {
            $payload['SHOPPER_MID']        = $shopperMid;
            $payload['SHOPPER_TID']        = (string) config('affiliates.datafast.shopper_tid', '');
            $payload['SHOPPER_ECI']        = (string) config('affiliates.datafast.shopper_eci', '0');
            $payload['SHOPPER_PSERV']      = (string) config('affiliates.datafast.shopper_pserv', '9999');
            $payload['SHOPPER_VERSIONDF']  = (string) config('affiliates.datafast.shopper_version', '2');
            $payload['SHOPPER_VAL_BASE0']  = number_format($base0, 2, '.', '');
            $payload['SHOPPER_VAL_BASEIMP']= number_format($baseImp, 2, '.', '');
            $payload['SHOPPER_VAL_IVA']    = number_format($iva, 2, '.', '');
        }

        if ($commerceName !== '') {
            $payload['risk.parameters[USER_DATA2]'] = $commerceName;
        }

        Log::info('Datafast: requesting checkoutId.', [
            'merchant_tx_id' => $merchantTransactionId,
            'amount'         => $amount,
            'user_id'        => $user->id,
        ]);

        $response = Http::withToken($this->bearerToken)
            ->timeout(30)
            ->asForm()
            ->post("{$this->baseUrl}/v1/checkouts", $payload);

        if (! $response->successful()) {
            Log::error('Datafast: checkout initiation failed.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException('Datafast checkout initiation failed. Status: ' . $response->status());
        }

        $data = $response->json();
        $checkoutId = $data['id'] ?? null;

        if (! is_string($checkoutId) || $checkoutId === '') {
            Log::error('Datafast: missing checkoutId in response.', ['body' => $response->body()]);
            throw new RuntimeException('Datafast did not return a valid checkoutId.');
        }

        Log::info('Datafast: checkoutId obtained.', ['checkout_id' => $checkoutId]);

        return $checkoutId;
    }

    /**
     * Verify a completed transaction using the resourcePath returned by Datafast.
     *
     * Returns the full response array from Datafast.
     *
     * @return array<string, mixed>
     * @throws RuntimeException on HTTP failure.
     */
    public function verifyTransaction(string $resourcePath): array
    {
        $url = "{$this->baseUrl}{$resourcePath}?entityId=" . urlencode($this->entityId);

        Log::info('Datafast: verifying transaction.', ['resource_path' => $resourcePath]);

        $response = Http::withToken($this->bearerToken)
            ->timeout(30)
            ->get($url);

        if (! $response->successful()) {
            Log::error('Datafast: transaction verification failed.', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException('Datafast transaction verification failed. Status: ' . $response->status());
        }

        $data = $response->json();

        Log::info('Datafast: verification response received.', [
            'result_code'        => $data['result']['code'] ?? 'n/a',
            'result_description' => $data['result']['description'] ?? 'n/a',
            'id'                 => $data['id'] ?? 'n/a',
            'full_body'          => $response->body(),
        ]);

        return $data;
    }

    /**
     * Whether the result code from Datafast indicates a successful payment.
     */
    public function isSuccessResult(string $resultCode): bool
    {
        return (bool) preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $resultCode)
            || (bool) preg_match('/^(000\.400\.[0-1]{2}0|000\.400\.020)/', $resultCode);
    }

    /**
     * Whether the result code indicates a pending/deferred payment.
     */
    public function isPendingResult(string $resultCode): bool
    {
        return (bool) preg_match('/^000\.200/', $resultCode);
    }

    /**
     * Extract the checkoutId embedded in a Datafast resourcePath.
     * resourcePath format: /v1/checkouts/{checkoutId}/payment
     */
    public function extractCheckoutIdFromResourcePath(string $resourcePath): ?string
    {
        if (preg_match('#/checkouts/([^/]+)/payment#', $resourcePath, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Split the total amount into BASE0, BASEIMP, and IVA for SRI declaration.
     * If IVA rate is 0, the full amount is treated as BASE0.
     *
     * @return array{float, float, float}  [base0, baseImp, iva]
     */
    private function splitTaxAmounts(float $total, float $ivaRate): array
    {
        if ($ivaRate <= 0) {
            return [$total, 0.0, 0.0];
        }

        $baseImp = round($total / (1 + $ivaRate), 2);
        $iva     = round($total - $baseImp, 2);

        // Correct rounding drift on the iva side
        if (abs($baseImp + $iva - $total) > 0.001) {
            $iva = round($total - $baseImp, 2);
        }

        return [0.0, $baseImp, $iva];
    }

    private function extractGivenName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName), 2);

        return $parts[0] ?? $fullName;
    }

    private function extractSurname(string $fullName): string
    {
        $parts = explode(' ', trim($fullName), 2);

        return $parts[1] ?? '';
    }
}
