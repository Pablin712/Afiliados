<?php

namespace App\Services;

use App\Support\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Works out which country a visitor is connecting from, so plans can charge
 * the price of that country's region the way streaming services do: the
 * buyer cannot pick, and a VPN moves them along with it.
 */
class VisitorCountryService
{
    /**
     * ISO code of the visitor's country, or null when it cannot be told (a
     * private address, or every lookup service failing) - in which case the
     * plan's base price applies.
     */
    public function detect(Request $request): ?string
    {
        $debugCountry = strtoupper(trim((string) config('affiliates.geo.debug_country')));

        if ($debugCountry !== '' && app()->isLocal() && Countries::isCode($debugCountry)) {
            return $debugCountry;
        }

        $ip = (string) $request->ip();

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        $cacheKey = 'geo:country:'.sha1($ip);
        $cached = Cache::get($cacheKey);

        // An empty string records a failed lookup, so it is not retried on
        // every page view while the services are down.
        if (is_string($cached)) {
            return $cached === '' ? null : $cached;
        }

        $country = $this->lookup($ip);

        Cache::put(
            $cacheKey,
            $country ?? '',
            now()->addMinutes($country === null
                ? (int) config('affiliates.geo.failure_cache_minutes', 2)
                : (int) config('affiliates.geo.cache_minutes', 360)),
        );

        return $country;
    }

    private function lookup(string $ip): ?string
    {
        $timeout = max(1, (int) config('affiliates.geo.timeout_seconds', 2));

        foreach ((array) config('affiliates.geo.providers', []) as $template) {
            $url = str_replace('{ip}', rawurlencode($ip), (string) $template);

            try {
                $response = Http::connectTimeout($timeout)->timeout($timeout)->get($url);
            } catch (Throwable $e) {
                Log::warning('Geolocation provider unreachable.', ['url' => $url, 'error' => $e->getMessage()]);

                continue;
            }

            if (! $response->successful()) {
                Log::warning('Geolocation provider error.', ['url' => $url, 'status' => $response->status()]);

                continue;
            }

            $json = $response->json();
            $answer = is_array($json)
                ? ($json['country'] ?? $json['country_code'] ?? null)
                : $response->body();

            $code = strtoupper(trim((string) $answer));

            // Anything else (an HTML error page, "XX", an anycast "EU") is not
            // a country a region can list.
            if (Countries::isCode($code)) {
                return $code;
            }
        }

        return null;
    }
}
