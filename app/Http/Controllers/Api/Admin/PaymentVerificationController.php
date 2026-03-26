<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PendingPaymentReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentVerificationController extends Controller
{
    public function __construct(private readonly PendingPaymentReviewService $pendingPaymentReviewService)
    {
    }

    public function pending(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $limit = (int) ($validated['limit'] ?? 50);

        $records = Payment::query()
            ->with(['user:id,name,email', 'transaction.bank:id,name,owner,identification,number,detail'])
            ->where('state', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $data = $records->map(function (Payment $payment): array {
            return [
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
        })->values();

        return response()->json([
            'message' => 'Pending payments retrieved successfully.',
            'meta' => [
                'count' => $data->count(),
                'limit' => $limit,
            ],
            'data' => $data,
        ]);
    }

    public function show(Payment $payment): JsonResponse
    {
        if ($payment->state !== 'pending') {
            return response()->json([
                'message' => 'Payment is not pending.',
                'meta' => [
                    'payment_id' => (int) $payment->id,
                    'state' => (string) $payment->state,
                ],
                'data' => null,
            ], 422);
        }

        $payment->load(['user:id,name,email', 'transaction.bank:id,name,owner,identification,number,detail']);

        return response()->json([
            'message' => 'Pending payment retrieved successfully.',
            'meta' => [
                'payment_id' => (int) $payment->id,
            ],
            'data' => [
                'event' => 'payment.pending',
                'payment_id' => (int) $payment->id,
                'user_id' => (int) $payment->user_id,
                'bank_id' => (int) ($payment->transaction?->bank?->id ?? 0),
                'payment_number' => (string) $payment->number,
                'amount' => (float) $payment->amount,
                'created_at' => optional($payment->created_at)?->toIso8601String(),
                'bank' => [
                    'id' => (int) ($payment->transaction?->bank?->id ?? 0),
                    'name' => (string) ($payment->transaction?->bank?->name ?? ''),
                    'owner' => (string) ($payment->transaction?->bank?->owner ?? ''),
                    'identification' => (string) ($payment->transaction?->bank?->identification ?? ''),
                    'number' => (string) ($payment->transaction?->bank?->number ?? ''),
                    'detail' => (string) ($payment->transaction?->bank?->detail ?? ''),
                ],
                'receipt_url' => route('api.admin.payments.pending.receipt', ['payment' => $payment->id]),
                'approve_url' => route('api.admin.payments.pending.approve', ['payment' => $payment->id]),
                'reject_url' => route('api.admin.payments.pending.reject', ['payment' => $payment->id]),
            ],
        ]);
    }

    public function receipt(Payment $payment): BinaryFileResponse|JsonResponse
    {
        if (! is_string($payment->photo) || trim($payment->photo) === '') {
            return response()->json([
                'message' => 'Payment receipt is not available.',
            ], 404);
        }

        if (! Storage::disk('public')->exists($payment->photo)) {
            return response()->json([
                'message' => 'Payment receipt file was not found.',
            ], 404);
        }

        return response()->file(Storage::disk('public')->path($payment->photo));
    }

    public function approve(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'reviewed_by' => ['nullable', 'integer', 'exists:users,id'],
            'trace_id' => ['nullable', 'string', 'max:120'],
            'ai_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'ai_errors' => ['nullable', 'string', 'max:500'],
            'gateway_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $detailParts = ['Auto-approved by n8n payment verifier'];

        if (isset($validated['trace_id']) && $validated['trace_id'] !== '') {
            $detailParts[] = 'trace_id='.$validated['trace_id'];
        }
        if (isset($validated['gateway_reference']) && $validated['gateway_reference'] !== '') {
            $detailParts[] = 'gateway_ref='.$validated['gateway_reference'];
        }
        if (isset($validated['ai_score'])) {
            $detailParts[] = 'ai_score='.$validated['ai_score'];
        }
        if (isset($validated['ai_errors']) && $validated['ai_errors'] !== '') {
            $detailParts[] = 'ai_errors='.$validated['ai_errors'];
        }

        try {
            $this->pendingPaymentReviewService->approve(
                $payment,
                isset($validated['reviewed_by']) ? (int) $validated['reviewed_by'] : null,
                implode(' | ', $detailParts)
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => 'Payment is already processed.',
                'meta' => [
                    'payment_id' => (int) $payment->id,
                    'state' => (string) $payment->state,
                ],
                'data' => null,
            ], 422);
        }

        return response()->json([
            'message' => 'Payment approved successfully.',
            'meta' => [
                'payment_id' => (int) $payment->id,
                'state' => 'approved',
            ],
            'data' => [
                'payment_id' => (int) $payment->id,
            ],
        ]);
    }

    public function reject(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'reviewed_by' => ['nullable', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->pendingPaymentReviewService->reject(
                $payment,
                isset($validated['reviewed_by']) ? (int) $validated['reviewed_by'] : null
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => 'Payment is already processed.',
                'meta' => [
                    'payment_id' => (int) $payment->id,
                    'state' => (string) $payment->state,
                ],
                'data' => null,
            ], 422);
        }

        return response()->json([
            'message' => 'Payment rejected successfully.',
            'meta' => [
                'payment_id' => (int) $payment->id,
                'state' => 'rejected',
                'reason' => (string) ($validated['reason'] ?? ''),
            ],
            'data' => [
                'payment_id' => (int) $payment->id,
            ],
        ]);
    }
}
