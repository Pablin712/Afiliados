<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $refCode = trim($request->string('ref')->toString());

        $sponsor = User::resolveAffiliateCode($refCode);

        if ($sponsor === null) {
            $sponsor = User::role('admin')->orderBy('id')->first()
                ?? User::query()->orderBy('id')->first();
        }

        abort_if($sponsor === null, 404);

        $banks = Bank::query()
            ->select(['id', 'name', 'owner', 'identification', 'number', 'detail'])
            ->orderBy('name')
            ->get();

        return view('auth.register', [
            'sponsor' => $sponsor,
            'banks' => $banks,
        ]);
    }

    /**
     * Check if registration fields are available.
     */
    public function availability(Request $request): JsonResponse
    {
        $email = Str::lower(trim($request->string('email')->toString()));
        $identification = trim($request->string('identification')->toString());

        $emailExists = $email !== ''
            ? User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()
            : false;

        $identificationExists = $identification !== ''
            ? User::query()->where('identification', $identification)->exists()
            : false;

        return response()->json([
            'email_exists' => $emailExists,
            'identification_exists' => $identificationExists,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'identification' => ['required', 'string', 'max:50', 'unique:'.User::class.',identification'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sponsor_id' => ['required', 'integer', 'exists:users,id'],
            'bank_id' => ['required', 'integer', 'exists:banks,id'],
            'payment_reference' => ['required', 'string', 'max:120'],
            'payment_amount' => ['required', 'numeric', 'in:147.00,147'],
            'payment_receipt' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($request): void {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'identification' => $request->string('identification')->toString(),
                'password' => Hash::make($request->password),
                'sponsor_id' => $request->integer('sponsor_id'),
                'approved_at' => null,
            ]);

            if (Schema::hasColumn('users', 'affiliate_code')) {
                $user->affiliate_code = User::buildAffiliateCode($user->name, $user->id);
                $user->save();
            }

            $user->assignRole('user');

            $bank = Bank::query()->lockForUpdate()->findOrFail($request->integer('bank_id'));
            $amount = (float) $request->input('payment_amount');

            $transaction = Transaction::create([
                'bank_id' => $bank->id,
                'type' => 'income',
                'amount_previous' => $bank->amount,
                'amount' => $amount,
                'amount_now' => $bank->amount,
                'detail' => __('messages.auth.pending_registration_transaction_detail', ['user' => $user->name]),
                'is_annulled' => true,
                'created_at' => now(),
            ]);

            Payment::create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'number' => $request->string('payment_reference')->toString(),
                'photo' => $request->file('payment_receipt')->store('payments/receipts', 'public'),
                'amount' => $amount,
                'state' => 'pending',
            ]);
        });

        return redirect(route('login'))->with('status', __('messages.auth.registration_pending_approval'));
    }
}
