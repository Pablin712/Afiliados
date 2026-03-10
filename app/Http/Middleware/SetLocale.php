<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Priority:
        // 1. Query parameter: ?locale=es
        // 2. Session: session('locale')
        // 3. Cookie: cookie('locale')
        // 4. Accept-Language header
        // 5. Default from config

        $locale = null;

        // Check query parameter
        if ($request->has('locale')) {
            $locale = $request->get('locale');
        }
        // Check session
        elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }
        // Check cookie
        elseif ($request->hasCookie('locale')) {
            $locale = $request->cookie('locale');
        }

        // Validate locale
        $availableLocales = ['en', 'es'];
        if ($locale && in_array($locale, $availableLocales)) {
            App::setLocale($locale);
            $request->session()->put('locale', $locale);
        } else {
            $locale = config('app.locale', 'en');
            App::setLocale($locale);
        }

        // Set cookie for persistent locale selection
        if ($request->has('locale')) {
            cookie()->queue('locale', $locale, 60 * 24 * 365); // 1 year
        }

        return $next($request);
    }
}
