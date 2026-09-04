<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', config('app.locale'));

        // Extract first 2 characters for language code
        $locale = substr((string) $locale, 0, 2);

        // Only allow supported locales
        if (in_array($locale, ['ar', 'en'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
