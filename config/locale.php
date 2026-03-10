<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Localization Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all configuration related to localization and
    | multi-language support for the affiliate system.
    |
    */

    // Available locales in the application
    'available_locales' => ['en', 'es'],

    // Locale name mapping for display
    'locale_names' => [
        'en' => 'English',
        'es' => 'Español',
    ],

    // Default locale when no specific locale is set
    'default_locale' => env('APP_LOCALE', 'en'),

    // Fallback locale if translation is missing
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

];
