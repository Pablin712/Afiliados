<?php

namespace App\Http\Middleware;

use App\Models\Membership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTelegramChatIdIsSet
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        // Skip profile and auth-related routes so the user can reach the page
        // where the Telegram code is displayed.
        if ($request->routeIs(
            'profile.edit',
            'profile.update',
            'profile.destroy',
            'profile.other-bank.update',
            'profile.telegram-chat-id.destroy',
            'logout',
            'auth.device-conflict.*'
        )) {
            return $next($request);
        }

        // Only enforce for active non-free members
        $user->loadMissing(['membership.membershipType']);
        $membership = $user->membership;

        if (! $membership instanceof Membership || $membership->status !== 'active') {
            return $next($request);
        }

        $typeName = strtolower((string) ($membership->membershipType?->name ?? ''));

        if ($typeName === '' || $typeName === 'free') {
            return $next($request);
        }

        if ($user->telegram_chat_id !== null) {
            return $next($request);
        }

        return redirect()
            ->route('profile.edit')
            ->with('telegram_required', __('messages.profile.telegram_required'));
    }
}
