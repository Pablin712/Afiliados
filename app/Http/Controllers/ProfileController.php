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
            'userBank' => $request->user()->userBanks()->first(),
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
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $binanceAccountId = trim((string) ($validated['binance_account_id'] ?? ''));
        $binanceUsername = trim((string) ($validated['binance_username'] ?? ''));

        if ($binanceAccountId !== '' && $binanceUsername !== '') {
            $userBank = $request->user()->userBanks()->first();

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
