<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class SetApiLocale { public function handle(Request $request, Closure $next): Response { $locale=substr((string)$request->header('Accept-Language','en'),0,2); app()->setLocale(in_array($locale,['ar','en'],true)?$locale:'en'); return $next($request); } }
