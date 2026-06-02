<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Single-device enforcement: detect existing active sessions for this user
        $user = Auth::user();

        if ($user && ! $user->hasRole('admin')) {
            $currentSessionId = $request->session()->getId();

            $activeThreshold = now()->subSeconds(config('session.lifetime', 120) * 60)->timestamp;

            $otherSession = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->whereNull('kicked_at')
                ->where('last_activity', '>=', $activeThreshold)
                ->orderByDesc('last_activity')
                ->first();

            if ($otherSession) {
                $request->session()->put('device_conflict', [
                    'session_id' => $otherSession->id,
                ]);

                return redirect()->route('auth.device-conflict.show');
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
