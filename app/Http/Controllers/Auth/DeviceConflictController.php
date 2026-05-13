<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\UserAgentParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeviceConflictController extends Controller
{
    /** Show the device-conflict interstitial page. */
    public function show(Request $request): View|RedirectResponse
    {
        $conflict = $request->session()->get('device_conflict');

        if (! $conflict) {
            return redirect()->route('dashboard');
        }

        $otherSession = DB::table('sessions')
            ->where('id', $conflict['session_id'])
            ->first();

        // If the other session no longer exists, just proceed
        if (! $otherSession) {
            $request->session()->forget('device_conflict');

            return redirect()->route('dashboard');
        }

        $parsed = UserAgentParser::parse((string) ($otherSession->user_agent ?? ''));

        return view('auth.device-conflict', [
            'browser'      => $parsed['browser'],
            'os'           => $parsed['os'],
            'ip'           => $otherSession->ip_address ?? '—',
            'lastActivity' => Carbon::createFromTimestamp((int) $otherSession->last_activity),
        ]);
    }

    /** Take over: mark old session as kicked, let this device proceed. */
    public function takeover(Request $request): RedirectResponse
    {
        $conflict = $request->session()->get('device_conflict');

        if ($conflict) {
            // Mark the other session as kicked (not deleted so Device A gets a friendly message)
            DB::table('sessions')
                ->where('id', $conflict['session_id'])
                ->whereNull('kicked_at')
                ->update(['kicked_at' => now()]);

            $request->session()->forget('device_conflict');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /** Cancel: log out this (new) device and go back to login. */
    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget('device_conflict');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
