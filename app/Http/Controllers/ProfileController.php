<?php

namespace App\Http\Controllers;

use App\Models\UserBank;
use App\Models\User;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'userBank' => $request->user()->userBanks()->where('type', 'binance')->first(),
            'otherBank' => $request->user()->userBanks()->where('type', 'other_bank')->first(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $binanceAccountId = trim((string) ($validated['binance_account_id'] ?? ''));
        $binanceUsername = trim((string) ($validated['binance_username'] ?? ''));

        if ($binanceAccountId !== '' && $binanceUsername !== '') {
            $userBank = $request->user()->userBanks()->where('type', 'binance')->first();

            $bankPayload = [
                'bank_name' => 'Binance',
                'owner' => $binanceUsername,
                'identification' => $binanceAccountId,
                'number' => $binanceAccountId,
                'type' => 'binance',
                'is_default' => true,
                'detail' => __('messages.profile.binance_detail_value', [
                    'account' => $binanceAccountId,
                    'username' => $binanceUsername,
                ]),
            ];

            if ($userBank instanceof UserBank) {
                $userBank->update($bankPayload);
            } else {
                $request->user()->userBanks()->create($bankPayload);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateOtherBank(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'other_bank_name'           => ['required', 'string', 'max:120'],
            'other_bank_owner'          => ['required', 'string', 'max:150'],
            'other_bank_number'         => ['required', 'string', 'max:80'],
            'other_bank_identification' => ['nullable', 'string', 'max:50'],
        ]);

        $otherBank = $request->user()->userBanks()->where('type', 'other_bank')->first();

        $payload = [
            'bank_name'      => $validated['other_bank_name'],
            'owner'          => $validated['other_bank_owner'],
            'number'         => $validated['other_bank_number'],
            'identification' => $validated['other_bank_identification'] ?? null,
            'type'           => 'other_bank',
            'is_default'     => false,
        ];

        if ($otherBank instanceof UserBank) {
            $otherBank->update($payload);
        } else {
            $request->user()->userBanks()->create($payload);
        }

        return Redirect::route('profile.edit')->with('status', 'other-bank-updated');
    }

    public function deleteTelegramChatId(Request $request): RedirectResponse
    {
        $request->user()->telegram_chat_id = null;
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'telegram-chat-id-removed');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $fallbackSponsorId = $this->resolveFallbackSponsorId($user);

        Auth::logout();

        DB::transaction(function () use ($user, $fallbackSponsorId): void {
            User::query()
                ->where('sponsor_id', $user->id)
                ->update(['sponsor_id' => $fallbackSponsorId]);

            $user->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function resolveFallbackSponsorId(User $user): int
    {
        $sponsorId = (int) ($user->sponsor_id ?? 0);

        if ($sponsorId > 0 && $sponsorId !== (int) $user->id) {
            return $sponsorId;
        }

        return (int) (User::query()->where('id', '!=', $user->id)->whereKey(1)->value('id')
            ?? User::query()->where('id', '!=', $user->id)->orderBy('id')->value('id')
            ?? $user->id);
    }
}
