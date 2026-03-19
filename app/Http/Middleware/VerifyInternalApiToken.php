<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('affiliates.internal_api_token', '');
        if ($expected === '') {
            return response()->json([
                'message' => 'Internal API token is not configured.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $provided = (string) $request->header('X-Internal-Token', '');

        if ($provided === '' && $request->bearerToken() !== null) {
            $provided = (string) $request->bearerToken();
        }

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'message' => 'Unauthorized internal API request.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
