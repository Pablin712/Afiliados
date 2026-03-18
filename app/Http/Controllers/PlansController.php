<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $membership = Membership::query()
            ->with('membershipType')
            ->where('user_id', $userId)
            ->first();

        $pendingPayment = Payment::query()
            ->where('user_id', $userId)
            ->where('state', 'pending')
            ->first();

        $paidTypes = MembershipType::query()
            ->where('name', '!=', 'free')
            ->orderBy('cost')
            ->get();

        $banks = Bank::query()->orderBy('name')->get();

        return view('plans.index', compact('membership', 'pendingPayment', 'paidTypes', 'banks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        if (Payment::query()->where('user_id', $userId)->where('state', 'pending')->exists()) {
            return back()->with('error', __('messages.plans.already_pending'));
        }

        $validated = $request->validate([
            'bank_id' => ['required', 'integer', 'exists:banks,id'],
            'number'  => ['required', 'string', 'max:120'],
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'photo'   => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($validated, $userId): void {
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
                'transaction_id' => $transaction->id,
                'number'         => $validated['number'],
                'photo'          => $photoPath,
                'amount'         => $validated['amount'],
                'state'          => 'pending',
            ]);
        });

        return back()->with('status', __('messages.plans.payment_submitted'));
    }
}
