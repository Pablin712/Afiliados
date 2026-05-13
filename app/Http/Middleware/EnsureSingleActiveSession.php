<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleActiveSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only check authenticated users; guests and admins are exempt
        if (! Auth::check() || Auth::user()?->hasRole('admin')) {
            return $next($request);
        }

        // Skip the conflict and logout routes to avoid redirect loops
        if ($request->routeIs('auth.device-conflict.*', 'logout')) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();

        $isKicked = DB::table('sessions')
            ->where('id', $sessionId)
            ->whereNotNull('kicked_at')
            ->exists();

        if ($isKicked) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('info', __('messages.auth.device.session_taken_over'));
        }

        return $next($request);
    }
}
