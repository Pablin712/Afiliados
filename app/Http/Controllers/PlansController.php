<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentPendingWebhookService;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function __construct(private readonly PaymentPendingWebhookService $paymentPendingWebhookService)
    {
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

        $activeDirectAffiliates = 0;
        $canFreeRenewToday = false;

        if (! $isAdmin && $membership instanceof Membership) {
            $activeDirectAffiliates = $this->countActiveDirectAffiliates((int) $membership->user_id);
            $canFreeRenewToday = $this->canFreeRenewToday($membership, $membershipTypeName, $membershipStatus, $activeDirectAffiliates);
        }

        $pendingPayment = null;

        if (! $isAdmin) {
            $pendingPayment = Payment::query()
                ->with('program')
                ->where('user_id', $userId)
                ->where('state', 'pending')
                ->first();
        }

        $programs = Program::query()
            ->with('membershipType')
            ->orderBy('first_payment_cost')
            ->when(! $isAdmin, fn ($query) => $query->where('is_active', true))
            ->get();

        $banks = Bank::query()->orderBy('name')->get();

        // Customer renewals/reactivations are monthly.
        $hasApprovedPayment = ! $isAdmin && $membershipTypeName === 'customer' && in_array($membershipStatus, ['active', 'expired'], true);

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
            'canFreeRenewToday',
            'activeDirectAffiliates'
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

        $isCustomerRenewal = $membershipTypeName === 'customer' && in_array($membershipStatus, ['active', 'expired'], true);

        $calculatedAmount = $isCustomerRenewal
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

        $activeDirectAffiliates = $this->countActiveDirectAffiliates((int) $membership->user_id);

        if (! $this->canFreeRenewToday($membership, $membershipTypeName, $membershipStatus, $activeDirectAffiliates)) {
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
        if ($membershipTypeName === 'customer') {
            return in_array($membershipStatus, ['active', 'expired'], true);
        }

        return $membershipTypeName === 'free' || $membershipStatus === 'free';
    }

    private function canFreeRenewToday(Membership $membership, string $membershipTypeName, string $membershipStatus, int $activeDirectAffiliates): bool
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

        $required = (int) ($membership->membershipType?->affiliates_required ?? 0);

        return $activeDirectAffiliates >= $required;
    }

    private function countActiveDirectAffiliates(int $sponsorId): int
    {
        return (int) DB::table('users as child')
            ->join('memberships as m', 'm.user_id', '=', 'child.id')
            ->join('membership_types as mt', 'mt.id', '=', 'm.membership_type_id')
            ->where('child.sponsor_id', $sponsorId)
            ->where('m.status', 'active')
            ->whereRaw('LOWER(mt.name) <> ?', ['free'])
            ->count();
    }
}

