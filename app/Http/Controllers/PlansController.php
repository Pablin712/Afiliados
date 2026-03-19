<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $userId = $user?->id;
        $isAdmin = $user instanceof User && $user->hasRole('admin');

        $membership = Membership::query()
            ->with('membershipType')
            ->where('user_id', $userId)
            ->first();

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

        // Determine if the user is renewing (has ever had an approved payment)
        $hasApprovedPayment = false;

        if (! $isAdmin) {
            $hasApprovedPayment = Payment::query()
                ->where('user_id', $userId)
                ->where('state', 'approved')
                ->exists();
        }

        return view('plans.index', compact('membership', 'pendingPayment', 'programs', 'banks', 'hasApprovedPayment', 'isAdmin'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('admin')) {
            return back()->with('error', __('messages.plans.admin_no_payment'));
        }

        $userId = Auth::id();

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

        $hasApprovedPayment = Payment::query()
            ->where('user_id', $userId)
            ->where('state', 'approved')
            ->exists();

        $calculatedAmount = $hasApprovedPayment
            ? (float) $program->renewal_cost
            : (float) $program->first_payment_cost;

        DB::transaction(function () use ($validated, $userId, $calculatedAmount): void {
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

            Payment::create([
                'user_id'        => $userId,
                'program_id'     => $validated['program_id'],
                'transaction_id' => $transaction->id,
                'number'         => $validated['number'],
                'photo'          => $photoPath,
                'amount'         => $calculatedAmount,
                'state'          => 'pending',
            ]);
        });

        return back()->with('status', __('messages.plans.payment_submitted'));
    }
}

