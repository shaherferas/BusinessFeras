<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRtlDirection
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $locale = app()->getLocale();

        // Set RTL direction for Arabic
        if ($locale === 'ar') {
            $response->headers->set('X-Direction', 'rtl');
        }

        return $response;
    }
}
