<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserPhoneIsSet
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($request->routeIs('profile.edit', 'profile.update', 'profile.destroy', 'logout', 'auth.device-conflict.*')) {
            return $next($request);
        }

        $phone = trim((string) ($user->phone ?? ''));

        if (! preg_match('/^\+?[1-9]\d{7,14}$/', $phone)) {
            return redirect()
                ->route('profile.edit')
                ->with('phone_required', __('messages.profile.phone_required'));
        }

        return $next($request);
    }
}
