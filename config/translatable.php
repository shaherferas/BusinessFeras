<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The locales that are supported by your application. When a model is
    | translatable, these are the locales that can be used.
    |
    */

    'supported_locales' => [
        'en',
        'ar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | This is the locale that will be used when the requested locale is not
    | available for a translatable attribute.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
];
