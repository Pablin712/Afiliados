<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Payment;
use App\Models\Program;
use App\Services\PayphoneService;
use App\Services\PendingPaymentReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PayphoneController extends Controller
{
    public function __construct(
        private readonly PayphoneService $payphoneService,
        private readonly PendingPaymentReviewService $pendingPaymentReviewService,
    ) {}

    /**
     * Initiate a Payphone payment for a program.
     * Called via AJAX from the plans page.
     */
    public function prepare(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $validated = $request->validate([
            'program_id' => ['required', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
        ]);

        $userId = (int) $user->id;

        $membership = Membership::query()
            ->with('membershipType')
            ->where('user_id', $userId)
            ->first();

        $membershipTypeName = strtolower((string) ($membership?->membershipType?->name ?? 'free'));
        $membershipStatus   = (string) ($membership?->status ?? 'free');

        if (! $this->canSubmitPayment($membershipTypeName, $membershipStatus)) {
            return response()->json(['message' => __('messages.plans.only_customer_or_free_can_pay')], 422);
        }

        if (Payment::query()->where('user_id', $userId)->where('state', 'pending')->exists()) {
            return response()->json(['message' => __('messages.plans.already_pending')], 422);
        }

        $program = Program::query()->findOrFail($validated['program_id']);

        $isRenewal = $membershipStatus !== 'free'
            && Payment::query()->where('user_id', $userId)->where('state', 'approved')->exists();

        $amount      = $isRenewal ? (float) $program->renewal_cost : (float) $program->first_payment_cost;
        $amountCents = (int) round($amount * 100);

        $clientTransactionId = (string) Str::uuid();

        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [$user->name];
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? $nameParts[0];

        try {
            $result = $this->payphoneService->prepare([
                'amount'               => $amountCents,
                'amountWithTax'        => 0,
                'tax'                  => 0,
                'amountWithoutTax'     => $amountCents,
                'currency'             => 'USD',
                'storeId'              => config('services.payphone.store_id', ''),
                'reference'            => 'AET-' . strtoupper(substr($clientTransactionId, 0, 8)),
                'clientTransactionId'  => $clientTransactionId,
                'responseUrl'          => route('plans.payphone.callback'),
                'cancellationUrl'      => route('plans.index'),
                'email'                => (string) $user->email,
                'documentId'           => (string) ($user->identification ?? ''),
                'phoneNumber'          => (string) ($user->phone ?? ''),
                'firstName'            => $firstName,
                'lastName'             => $lastName,
                'description'          => $program->name . ' — AET',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => __('messages.plans.payphone_prepare_error')], 500);
        }

        Payment::create([
            'user_id'                       => $userId,
            'program_id'                    => $validated['program_id'],
            'transaction_id'                => null,
            'number'                        => $clientTransactionId,
            'photo'                         => null,
            'amount'                        => $amount,
            'state'                         => 'pending',
            'payment_method'                => 'payphone',
            'payphone_client_transaction_id' => $clientTransactionId,
        ]);

        return response()->json([
            'payment_id'            => $result['paymentId'],
            'client_transaction_id' => $clientTransactionId,
        ]);
    }

    /**
     * Payphone redirects the user here after payment (success or failure).
     * We confirm with Payphone API and auto-approve if valid.
     */
    public function callback(Request $request): RedirectResponse
    {
        $id                   = (string) $request->query('id', '');
        $clientTransactionId  = (string) $request->query('clientTransactionId', '');
        $transactionStatus    = (string) $request->query('transactionStatus', '');

        if ($id === '' || $clientTransactionId === '') {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.payphone_invalid_callback'));
        }

        $payment = Payment::query()
            ->where('payphone_client_transaction_id', $clientTransactionId)
            ->where('state', 'pending')
            ->first();

        if (! $payment instanceof Payment) {
            return redirect()->route('plans.index')
                ->with('status', __('messages.plans.payphone_already_processed'));
        }

        if ($transactionStatus !== 'Approved') {
            $payment->state       = 'rejected';
            $payment->reviewed_at = now();
            $payment->save();

            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.payphone_declined'));
        }

        try {
            $confirmed = $this->payphoneService->confirm($id, $clientTransactionId);
        } catch (\Throwable) {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.payphone_confirm_error'));
        }

        if (($confirmed['transactionStatus'] ?? '') !== 'Approved') {
            $payment->state       = 'rejected';
            $payment->reviewed_at = now();
            $payment->save();

            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.payphone_declined'));
        }

        try {
            $this->pendingPaymentReviewService->approve($payment, null);
        } catch (\Throwable) {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.payphone_confirm_error'));
        }

        return redirect()->route('plans.index')
            ->with('status', __('messages.plans.payphone_approved'));
    }

    private function canSubmitPayment(string $membershipTypeName, string $membershipStatus): bool
    {
        if ($membershipTypeName === '') {
            return false;
        }

        return in_array($membershipStatus, ['active', 'expired', 'free'], true);
    }
}
