<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class TranslationHelper
{
    /**
     * Get a translation string
     *
     * @param string $key The translation key (e.g., 'messages.welcome')
     * @param array $replace Values to replace in the translated string
     * @param string|null $locale The locale to use (optional)
     * @return string
     */
    public static function get($key, $replace = [], $locale = null)
    {
        return trans($key, $replace, $locale);
    }

    /**
     * Get the current application locale
     *
     * @return string
     */
    public static function getCurrentLocale()
    {
        return App::getLocale();
    }

    /**
     * Set the application locale
     *
     * @param string $locale
     * @return void
     */
    public static function setLocale($locale)
    {
        App::setLocale($locale);
    }

    /**
     * Get available locales
     *
     * @return array
     */
    public static function getAvailableLocales()
    {
        return ['en', 'es'];
    }

    /**
     * Translate a key with default value
     *
     * @param string $key
     * @param string $default
     * @param array $replace
     * @return string
     */
    public static function getOrDefault($key, $default = '', $replace = [])
    {
        $translated = trans($key, $replace);
        return ($translated === $key) ? $default : $translated;
    }

    /**
     * Check if a translation key exists
     *
     * @param string $key
     * @param string|null $locale
     * @return bool
     */
    public static function hasKey($key, $locale = null)
    {
        return trans_has($key, $locale ?? App::getLocale());
    }
}
