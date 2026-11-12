<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps users.last_seen_at current so the admin can see how long an account
 * has gone unused.
 */
class TrackLastSeen
{
    /** Minutes between writes: plenty for "inactive for months". */
    private const INTERVAL_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User
            && ($user->last_seen_at === null || $user->last_seen_at->lt(now()->subMinutes(self::INTERVAL_MINUTES)))) {
            $now = now();

            // Straight to the table: not a change worth an updated_at bump or
            // an audit entry on every few minutes of browsing.
            DB::table('users')->where('id', $user->id)->update(['last_seen_at' => $now]);
            $user->last_seen_at = $now;
            $user->syncOriginalAttribute('last_seen_at');
        }

        return $next($request);
    }
}
