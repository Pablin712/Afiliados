<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ScannerDownloadService;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScannerDownloadController extends Controller
{
    private const XQUANT_BASE_URL = 'https://xquant.marketview.club';

    private const XQUANT_SCANNER_REGISTER_URL = 'https://xquant.marketview.club/api/auth/scanner/register';

    public function __construct(private readonly ScannerDownloadService $scannerDownloadService)
    {
    }

    public function registerDerivAndRedirect(Request $request): RedirectResponse
    {
        $user = $request->user();

        $membershipTypeName = strtolower((string) ($user->membership?->membershipType?->name ?? 'free'));
        $isFreeUser = ! $user->hasRole('admin') && $membershipTypeName === 'free';

        if ($isFreeUser) {
            $windowEnd = $user->created_at->copy()->addHours(12);
            if (now()->gte($windowEnd)) {
                return redirect()->route('dashboard')
                    ->with('error', __('messages.user.dashboard.scanner.free_window_expired'));
            }
        }

        $targetUrl = self::XQUANT_BASE_URL;

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(12)
                ->post(self::XQUANT_SCANNER_REGISTER_URL, [
                    'email' => $user->email,
                ]);

            if (! $response->successful()) {
                Log::warning('xquant scanner register failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $throwable) {
            Log::warning('xquant scanner register request exception', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $throwable->getMessage(),
            ]);
        }

        return redirect()->away($targetUrl);
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
