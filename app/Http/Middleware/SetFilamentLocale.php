<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetFilamentLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('filament_locale', config('app.locale', 'en'));

        // Validate against supported locales
        $supportedLocales = config('translatable.supported_locales', ['en', 'ar']);

        if (in_array($locale, $supportedLocales)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
