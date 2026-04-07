<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ScannerDownloadService;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScannerDownloadController extends Controller
{
    public function __construct(private readonly ScannerDownloadService $scannerDownloadService)
    {
    }

    public function prepare(Request $request): JsonResponse
    {
        try {
            $this->ensureEligibleMembership($request);
            $this->scannerDownloadService->ensureCompilerConfigured();
        } catch (RuntimeException $exception) {
            return $this->scannerErrorResponse($exception->getMessage());
        }

        $validated = $request->validate([
            'broker' => ['required', 'string', 'in:deriv,weltrade'],
            'account_id' => ['required', 'digits:8'],
        ]);

        if (! $request->user()->membership?->expires_at) {
            return $this->scannerErrorResponse(__('messages.user.dashboard.scanner.membership_expiration_missing'));
        }

        $patterns = $this->scannerDownloadService->patternsForBroker($validated['broker']);

        $downloads = collect($patterns)
            ->map(function (string $pattern) use ($validated): array {
                return [
                    'pattern' => $pattern,
                    'url' => URL::temporarySignedRoute(
                        'scanners.download',
                        now()->addMinutes(5),
                        [
                            'broker' => $validated['broker'],
                            'pattern' => strtolower($pattern),
                            'account_id' => $validated['account_id'],
                        ]
                    ),
                ];
            })
            ->values();

        return response()->json([
            'downloads' => $downloads,
        ]);
    }

    public function download(Request $request, string $broker, string $pattern): Response|StreamedResponse
    {
        try {
            $this->ensureEligibleMembership($request);
        } catch (RuntimeException $exception) {
            return $this->scannerErrorResponse($exception->getMessage());
        }

        $validator = validator([
            'broker' => $broker,
            'pattern' => $pattern,
            'account_id' => (string) $request->query('account_id', ''),
        ], [
            'broker' => ['required', 'string', 'in:deriv,weltrade'],
            'pattern' => ['required', 'string'],
            'account_id' => ['required', 'digits:8'],
        ]);

        if ($validator->fails()) {
            return $this->scannerErrorResponse((string) $validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $compiled = $this->scannerDownloadService->buildScanner(
                $request->user(),
                $validated['broker'],
                $validated['pattern'],
                $validated['account_id']
            );
        } catch (RuntimeException $exception) {
            return $this->scannerErrorResponse($exception->getMessage());
        }

        return response()->streamDownload(
            static function () use ($compiled): void {
                echo $compiled['content'];
            },
            $compiled['fileName'],
            [
                'Content-Type' => 'application/octet-stream',
            ]
        );
    }

    private function ensureEligibleMembership(Request $request): void
    {
        $membership = $request->user()->membership;
        $membershipTypeName = strtolower((string) ($membership?->membershipType?->name ?? 'free'));

        if ($membershipTypeName === 'free') {
            throw new RuntimeException(__('messages.user.dashboard.scanner.membership_not_eligible'));
        }

        $expiresAt = $membership?->expires_at;
        if (! $expiresAt instanceof CarbonInterface) {
            throw new RuntimeException(__('messages.user.dashboard.scanner.membership_expiration_missing'));
        }

        if ($expiresAt->isPast()) {
            throw new RuntimeException(__('messages.user.dashboard.scanner.membership_expired'));
        }
    }

    private function scannerErrorResponse(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => [
                'scanner' => [$message],
            ],
        ], 422);
    }
}
