<?php

namespace App\Support;

final class UserAgentParser
{
    public static function parse(string $userAgent): array
    {
        return [
            'browser' => self::browser($userAgent),
            'os'      => self::os($userAgent),
        ];
    }

    public static function browser(string $ua): string
    {
        if (str_contains($ua, 'Edg/') || str_contains($ua, 'EdgA/')) {
            return 'Edge';
        }
        if (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera/')) {
            return 'Opera';
        }
        if (str_contains($ua, 'SamsungBrowser/')) {
            return 'Samsung Internet';
        }
        if (str_contains($ua, 'Chrome/') && !str_contains($ua, 'Chromium/')) {
            return 'Chrome';
        }
        if (str_contains($ua, 'Firefox/')) {
            return 'Firefox';
        }
        if (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) {
            return 'Safari';
        }
        if (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident/')) {
            return 'Internet Explorer';
        }

        return 'Navegador desconocido';
    }

    public static function os(string $ua): string
    {
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') || str_contains($ua, 'iPod')) {
            return 'iOS';
        }
        if (str_contains($ua, 'Android')) {
            return 'Android';
        }
        if (str_contains($ua, 'Windows NT')) {
            return 'Windows';
        }
        if (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS X')) {
            return 'macOS';
        }
        if (str_contains($ua, 'Linux')) {
            return 'Linux';
        }
        if (str_contains($ua, 'CrOS')) {
            return 'ChromeOS';
        }

        return 'Sistema desconocido';
    }

    /** Returns a short readable label: "Chrome · Windows" */
    public static function label(string $userAgent): string
    {
        $parsed = self::parse($userAgent);

        return $parsed['browser'].' · '.$parsed['os'];
    }
}
