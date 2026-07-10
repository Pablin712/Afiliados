<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DatafastService;
use App\Services\PaymentPendingWebhookService;
use App\Services\PendingPaymentReviewService;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PlansController extends Controller
{
    public function __construct(
        private readonly PaymentPendingWebhookService $paymentPendingWebhookService,
        private readonly PendingPaymentReviewService $pendingPaymentReviewService,
        private readonly DatafastService $datafastService,
    ) {
    }

    public function index(): View
    {
        $user = Auth::user();
        $userId = $user?->id;
        $isAdmin = $user instanceof User && $user->hasRole('admin');

        $membership = Membership::query()
            ->with(['membershipType', 'lastPayment.program'])
            ->where('user_id', $userId)
            ->first();

        $membershipTypeName = strtolower((string) ($membership?->membershipType?->name ?? 'free'));
        $membershipStatus = (string) ($membership?->status ?? 'free');
        $canSubmitPaidRenewal = ! $isAdmin && $this->canSubmitPaidRenewal($membershipTypeName, $membershipStatus);
        $isTierUser = ! in_array($membershipTypeName, ['free', 'customer', ''], true);

        $monthlyNewDirectCustomers = 0;
        $canFreeRenewToday = false;

        if (! $isAdmin && $membership instanceof Membership) {
            $monthlyNewDirectCustomers = $this->countMonthlyNewDirectCustomers((int) $membership->user_id, now());
            $canFreeRenewToday = $this->canFreeRenewToday($membership, $membershipTypeName, $membershipStatus, $monthlyNewDirectCustomers);
        }

        $pendingPayment = null;

        if (! $isAdmin) {
            // Only bank-transfer payments need admin review and show the "pending" UI.
            // Card payments are either auto-approved, rejected, or cancelled on failure.
            $pendingPayment = Payment::query()
                ->with('program')
                ->where('user_id', $userId)
                ->where('state', 'pending')
                ->where('payment_method', 'bank_transfer')
                ->first();
        }

        $programs = Program::query()
            ->with('membershipType')
            ->orderBy('first_payment_cost')
            ->when(! $isAdmin, fn ($query) => $query->where('is_active', true))
            ->get();

        $banks = Bank::query()->orderBy('name')->get();

        // Renewals/reactivations use renewal cost for users with previous approved payments.
        $hasApprovedPayment = ! $isAdmin
            && Payment::query()->where('user_id', $userId)->where('state', 'approved')->exists();

        return view('plans.index', compact(
            'membership',
            'pendingPayment',
            'programs',
            'banks',
            'hasApprovedPayment',
            'isAdmin',
            'membershipTypeName',
            'membershipStatus',
            'canSubmitPaidRenewal',
            'isTierUser',
            'canFreeRenewToday',
            'monthlyNewDirectCustomers'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('admin')) {
            return back()->with('error', __('messages.plans.admin_no_payment'));
        }

        $userId = Auth::id();

        $membership = Membership::query()
            ->with('membershipType')
            ->where('user_id', $userId)
            ->first();

        $membershipTypeName = strtolower((string) ($membership?->membershipType?->name ?? 'free'));
        $membershipStatus = (string) ($membership?->status ?? 'free');

        if (! $this->canSubmitPaidRenewal($membershipTypeName, $membershipStatus)) {
            return back()->with('error', __('messages.plans.only_customer_or_free_can_pay'));
        }

        if (Payment::query()->where('user_id', $userId)->where('state', 'pending')->exists()) {
            return back()->with('error', __('messages.plans.already_pending'));
        }

        $validated = $request->validate([
            'program_id' => ['required', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
            'bank_id'    => ['required', 'integer', 'exists:banks,id'],
            'number'     => ['required', 'string', 'max:120'],
            'photo'      => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $program = Program::query()->findOrFail($validated['program_id']);

        $isRenewalOrReactivation = Payment::query()->where('user_id', $userId)->where('state', 'approved')->exists();

        $calculatedAmount = $isRenewalOrReactivation
            ? (float) $program->renewal_cost
            : (float) $program->first_payment_cost;

        $payment = DB::transaction(function () use ($validated, $userId, $calculatedAmount): Payment {
            $photoPath = $validated['photo']->store('payment-receipts', 'public');

            $transaction = Transaction::create([
                'bank_id'         => $validated['bank_id'],
                'type'            => 'income',
                'amount_previous' => 0,
                'amount'          => 0,
                'amount_now'      => 0,
                'detail'          => null,
                'is_annulled'     => false,
                'created_at'      => now(),
            ]);

            return Payment::create([
                'user_id'        => $userId,
                'program_id'     => $validated['program_id'],
                'transaction_id' => $transaction->id,
                'number'         => $validated['number'],
                'photo'          => $photoPath,
                'amount'         => $calculatedAmount,
                'state'          => 'pending',
            ]);
        });

        $this->paymentPendingWebhookService->send($payment);

        return back()->with('status', __('messages.plans.payment_submitted'));
    }

    public function initiateCardCheckout(Request $request): RedirectResponse
    {
        if (! (bool) config('affiliates.datafast.enabled')) {
            return back()->with('error', __('messages.plans.card_payment_disabled'));
        }

        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('admin')) {
            return back()->with('error', __('messages.plans.admin_no_payment'));
        }

        if ($user instanceof User && (trim((string) $user->address) === '' || trim((string) $user->identification) === '')) {
            return redirect()->route('profile.edit')->with('address_required', __('messages.profile.address_required'));
        }

        $userId = Auth::id();

        $membership = Membership::query()
            ->with('membershipType')
            ->where('user_id', $userId)
            ->first();

        $membershipTypeName = strtolower((string) ($membership?->membershipType?->name ?? 'free'));
        $membershipStatus   = (string) ($membership?->status ?? 'free');

        if (! $this->canSubmitPaidRenewal($membershipTypeName, $membershipStatus)) {
            return back()->with('error', __('messages.plans.only_customer_or_free_can_pay'));
        }

        // Bank-transfer pending blocks the flow (awaits admin review).
        if (Payment::query()->where('user_id', $userId)->where('state', 'pending')->where('payment_method', 'bank_transfer')->exists()) {
            return back()->with('error', __('messages.plans.already_pending'));
        }

        // Cancel abandoned card sessions so the user can start a new one.
        Payment::query()
            ->where('user_id', $userId)
            ->where('state', 'pending')
            ->where('payment_method', 'card')
            ->update(['state' => 'cancelled']);

        $validated = $request->validate([
            'program_id' => ['required', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
        ]);

        $program = Program::query()->findOrFail($validated['program_id']);

        $isRenewalOrReactivation = Payment::query()->where('user_id', $userId)->where('state', 'approved')->exists();

        $cardAmount = $isRenewalOrReactivation
            ? $program->card_renewal_cost
            : $program->card_first_payment_cost;

        if ($cardAmount === null) {
            return back()->with('error', __('messages.plans.card_payment_disabled'));
        }

        $amount = (float) $cardAmount;

        $merchantTransactionId = 'AFL-' . $userId . '-' . now()->format('YmdHis');

        // Datafast will append ?resourcePath=... to this URL after payment.
        $shopperResultUrl = route('plans.card-return');

        try {
            $checkoutId = $this->datafastService->initiateCheckout(
                $amount,
                $user,
                $shopperResultUrl,
                $merchantTransactionId
            );
        } catch (RuntimeException $e) {
            Log::error('Datafast checkout initiation error.', ['error' => $e->getMessage(), 'user_id' => $userId]);

            return back()->with('error', __('messages.plans.card_checkout_failed'));
        }

        $payment = Payment::create([
            'user_id'          => $userId,
            'program_id'       => $program->id,
            'transaction_id'   => null,
            'number'           => $merchantTransactionId,
            'photo'            => null,
            'amount'           => $amount,
            'state'            => 'pending',
            'payment_method'   => 'card',
            'gateway_order_id' => $checkoutId,
        ]);

        return redirect()->route('plans.card-payment', ['payment' => $payment->id]);
    }

    public function showCardPayment(Request $request, Payment $payment): View|RedirectResponse
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        if ($payment->payment_method !== 'card' || $payment->state !== 'pending') {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.card_session_invalid'));
        }

        $checkoutId = (string) $payment->gateway_order_id;

        if ($checkoutId === '') {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.card_session_invalid'));
        }

        $widgetUrl = rtrim((string) config('affiliates.datafast.base_url'), '/')
            . '/v1/paymentWidgets.js?checkoutId=' . urlencode($checkoutId);

        $brands = (string) config('affiliates.datafast.brands', 'VISA MASTER DINERS AMEX');

        return view('plans.card-payment', compact('payment', 'checkoutId', 'widgetUrl', 'brands'));
    }

    public function cardPaymentReturn(Request $request): RedirectResponse
    {
        $resourcePath = trim((string) $request->input('resourcePath', ''));

        if ($resourcePath === '') {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.card_return_invalid'));
        }

        $checkoutId = $this->datafastService->extractCheckoutIdFromResourcePath($resourcePath);

        if ($checkoutId === null) {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.card_return_invalid'));
        }

        $payment = Payment::query()
            ->where('gateway_order_id', $checkoutId)
            ->where('payment_method', 'card')
            ->where('state', 'pending')
            ->first();

        if ($payment === null) {
            return redirect()->route('plans.index')
                ->with('error', __('messages.plans.card_return_invalid'));
        }

        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        // Dev-only bypass: skip real verification and simulate approval for local Fase 1 testing.
        if ((bool) config('affiliates.datafast.dev_bypass') && app()->isLocal()) {
            Log::warning('Datafast: DEV BYPASS active — skipping real verification, simulating approval.', [
                'payment_id'   => $payment->id,
                'checkout_id'  => $checkoutId,
            ]);
            $resultCode = '000.000.000';
            $gatewayId  = $checkoutId;
        } else {
            try {
                $result     = $this->datafastService->verifyTransaction($resourcePath);
                $resultCode = (string) ($result['result']['code'] ?? '');
                $gatewayId  = (string) ($result['id'] ?? $checkoutId);
            } catch (RuntimeException $e) {
                Log::error('Datafast: verification error on return.', [
                    'error'      => $e->getMessage(),
                    'payment_id' => $payment->id,
                ]);

                $payment->state = 'cancelled';
                $payment->save();

                return redirect()->route('plans.index')
                    ->with('error', __('messages.plans.card_verification_failed'));
            }
        }

        if ($this->datafastService->isSuccessResult($resultCode)) {
            $payment->number = $gatewayId;
            $payment->save();

            try {
                $this->pendingPaymentReviewService->approve($payment, reviewedBy: null);
            } catch (RuntimeException $e) {
                Log::error('Datafast: auto-approve failed after successful payment.', [
                    'payment_id' => $payment->id,
                    'error'      => $e->getMessage(),
                ]);

                return redirect()->route('plans.index')
                    ->with('status', __('messages.plans.card_paid_pending_activation'));
            }

            return redirect()->route('plans.index')
                ->with('status', __('messages.plans.card_payment_approved'));
        }

        if ($this->datafastService->isPendingResult($resultCode)) {
            $payment->number = $gatewayId;
            $payment->save();

            return redirect()->route('plans.index')
                ->with('status', __('messages.plans.card_payment_pending_bank'));
        }

        $payment->state = 'rejected';
        $payment->reviewed_at = now();
        $payment->save();

        return redirect()->route('plans.index')
            ->with('error', __('messages.plans.card_payment_rejected'));
    }

    public function renewForFree(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->hasRole('admin')) {
            return back()->with('error', __('messages.plans.admin_no_payment'));
        }

        $membership = Membership::query()
            ->with('membershipType')
            ->where('user_id', (int) $user?->id)
            ->first();

        if (! $membership instanceof Membership) {
            return back()->with('error', __('messages.plans.free_renew_not_eligible'));
        }

        $membershipTypeName = strtolower((string) ($membership->membershipType?->name ?? ''));
        $membershipStatus = (string) $membership->status;

        if (Payment::query()->where('user_id', (int) $membership->user_id)->where('state', 'pending')->exists()) {
            return back()->with('error', __('messages.plans.already_pending'));
        }

        $monthlyNewDirectCustomers = $this->countMonthlyNewDirectCustomers((int) $membership->user_id, now());

        if (! $this->canFreeRenewToday($membership, $membershipTypeName, $membershipStatus, $monthlyNewDirectCustomers)) {
            return back()->with('error', __('messages.plans.free_renew_not_eligible'));
        }

        $baseDate = $membership->expires_at instanceof Carbon
            ? $membership->expires_at->copy()
            : now();

        $membership->expires_at = $baseDate->addMonth();
        $membership->status = 'active';
        $membership->save();

        return back()->with('status', __('messages.plans.free_renew_success', [
            'date' => $membership->expires_at?->format('d/m/Y'),
        ]));
    }

    private function canSubmitPaidRenewal(string $membershipTypeName, string $membershipStatus): bool
    {
        if ($membershipTypeName === '') {
            return false;
        }

        return in_array($membershipStatus, ['active', 'expired', 'free'], true);
    }

    private function canFreeRenewToday(Membership $membership, string $membershipTypeName, string $membershipStatus, int $monthlyNewDirectCustomers): bool
    {
        if (in_array($membershipTypeName, ['free', 'customer', ''], true)) {
            return false;
        }

        if ($membershipStatus !== 'active') {
            return false;
        }

        if (! $membership->expires_at instanceof Carbon || ! $membership->expires_at->isSameDay(now())) {
            return false;
        }

        $required = 3;

        return $monthlyNewDirectCustomers >= $required;
    }

    private function countMonthlyNewDirectCustomers(int $sponsorId, Carbon $referenceDate): int
    {
        $monthStart = $referenceDate->copy()->startOfMonth();
        $monthEnd = $referenceDate->copy()->endOfMonth();

        return (int) DB::table('payments as p')
            ->join('users as direct', 'direct.id', '=', 'p.user_id')
            ->join('programs as pr', 'pr.id', '=', 'p.program_id')
            ->join('membership_types as mt', 'mt.id', '=', 'pr.membership_type_id')
            ->where('direct.sponsor_id', $sponsorId)
            ->where('p.state', 'approved')
            ->whereBetween('p.reviewed_at', [$monthStart, $monthEnd])
            ->whereRaw('LOWER(mt.name) = ?', ['customer'])
            ->whereNotExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('payments as prev')
                    ->whereColumn('prev.user_id', 'p.user_id')
                    ->where('prev.state', 'approved')
                    ->whereColumn('prev.id', '<', 'p.id');
            })
            ->count();
    }
}

