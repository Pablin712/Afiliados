<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Program;
use App\Models\ProgramRegionPrice;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DatafastService;
use App\Services\MembershipFreeRenewalService;
use App\Services\PaymentPendingWebhookService;
use App\Services\PendingPaymentReviewService;
use App\Services\VisitorCountryService;
use App\Support\Countries;
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
        private readonly MembershipFreeRenewalService $membershipFreeRenewalService,
        private readonly VisitorCountryService $visitorCountryService,
    ) {
    }

    public function index(Request $request): View
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
            $monthlyNewDirectCustomers = $this->membershipFreeRenewalService->currentPeriodReferralCount($membership);
            $canFreeRenewToday = $this->canFreeRenewToday($membership, $membershipTypeName, $membershipStatus);
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
            ->with(['membershipType', 'regionPrices'])
            ->orderBy('first_payment_cost')
            ->when(! $isAdmin, fn ($query) => $query->where('is_active', true))
            ->get();

        $banks = Bank::query()->orderBy('name')->get();

        // Only worth locating the buyer when some plan prices by region.
        $visitorCountry = ! $isAdmin && $programs->contains(fn (Program $program) => $program->regionPrices->isNotEmpty())
            ? $this->visitorCountryService->detect($request)
            : null;
        $visitorCountryName = $visitorCountry !== null ? Countries::name($visitorCountry) : null;

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
            'monthlyNewDirectCustomers',
            'visitorCountry',
            'visitorCountryName'
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
            'program_id'      => ['required', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
            'bank_id'         => ['required', 'integer', 'exists:banks,id'],
            'number'          => ['required', 'string', 'max:120'],
            'photo'           => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'region_price_id' => ['nullable', 'integer'],
        ]);

        $program = Program::query()->findOrFail($validated['program_id']);

        $country = $this->visitorCountryFor($program, $request);
        $regionPrice = $this->regionPriceForVisitor($program, $country, $request->input('region_price_id'));

        if ($regionPrice === false) {
            return back()->with('error', __('messages.plans.region_changed'));
        }

        $isRenewalOrReactivation = Payment::query()->where('user_id', $userId)->where('state', 'approved')->exists();

        // Never trust an amount coming from the form: it is recalculated here
        // from the program and the region of the buyer's country.
        $calculatedAmount = $program->transferCostFor($regionPrice, $isRenewalOrReactivation);
        $regionLabel = $regionPrice?->region;

        $payment = DB::transaction(function () use ($validated, $userId, $calculatedAmount, $regionLabel, $country): Payment {
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
                'region_label'   => $regionLabel,
                'country_code'   => $country,
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
            'program_id'      => ['required', 'integer', Rule::exists('programs', 'id')->where('is_active', true)],
            'region_price_id' => ['nullable', 'integer'],
        ]);

        $program = Program::query()->findOrFail($validated['program_id']);

        $country = $this->visitorCountryFor($program, $request);
        $regionPrice = $this->regionPriceForVisitor($program, $country, $request->input('region_price_id'));

        if ($regionPrice === false) {
            return back()->with('error', __('messages.plans.region_changed'));
        }

        $isRenewalOrReactivation = Payment::query()->where('user_id', $userId)->where('state', 'approved')->exists();

        $cardAmount = $program->cardCostFor($regionPrice, $isRenewalOrReactivation);

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
            'region_label'     => $regionPrice?->region,
            'country_code'     => $country,
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

        if (! $this->canFreeRenewToday($membership, $membershipTypeName, $membershipStatus)) {
            return back()->with('error', __('messages.plans.free_renew_not_eligible'));
        }

        // Open a new period starting exactly where the previous one ended (matching
        // MembershipExpiryService's automatic renewal) so RankBonusService's
        // isInitialActivationPeriod()/maintenance-bonus tracking — which is keyed off
        // started_at — keeps working correctly across repeated free renewals.
        $baseDate = $membership->expires_at instanceof Carbon
            ? $membership->expires_at->copy()
            : now();

        $membership->started_at = $baseDate->copy();
        $membership->expires_at = $baseDate->copy()->addMonth();
        $membership->status = 'active';
        $membership->renewal_count = ((int) $membership->renewal_count) + 1;
        $membership->save();

        return back()->with('status', __('messages.plans.free_renew_success', [
            'date' => $membership->expires_at?->format('d/m/Y'),
        ]));
    }

    /**
     * Country the buyer is paying from, looked up only for programs that
     * price by region - the others charge the same everywhere.
     */
    private function visitorCountryFor(Program $program, Request $request): ?string
    {
        return $program->regionPrices->isNotEmpty()
            ? $this->visitorCountryService->detect($request)
            : null;
    }

    /**
     * The region row that prices this buyer, which their country decides -
     * never the form. The form only echoes back the region the page was
     * priced with: if it no longer matches (a VPN switched on or off between
     * loading the page and paying) this returns false and the caller sends
     * the buyer back to see the new price, instead of charging an amount the
     * page never showed. Null means the program's own costs apply.
     */
    private function regionPriceForVisitor(Program $program, ?string $country, mixed $shownRegionId): ProgramRegionPrice|null|false
    {
        $regionPrice = $program->regionPriceForCountry($country);

        if ((string) ($regionPrice?->id ?? '') !== trim((string) $shownRegionId)) {
            return false;
        }

        return $regionPrice;
    }

    private function canSubmitPaidRenewal(string $membershipTypeName, string $membershipStatus): bool
    {
        if ($membershipTypeName === '') {
            return false;
        }

        return in_array($membershipStatus, ['active', 'expired', 'free'], true);
    }

    private function canFreeRenewToday(Membership $membership, string $membershipTypeName, string $membershipStatus): bool
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

        return $this->membershipFreeRenewalService->qualifies($membership);
    }
}

