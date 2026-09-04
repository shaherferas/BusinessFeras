<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Http\Middleware\SetApiLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', SetApiLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API callers must receive JSON 401/403 responses, never a redirect to a web login route.
        $exceptions->shouldRenderJsonWhen(fn (Request $request, \Throwable $exception) => $request->is('api/*') || $request->expectsJson());
        $exceptions->render(function (ValidationException $exception, Request $request) { if ($request->is('api/*')) return response()->json(['status'=>422,'message'=>'Validation failed.','errors'=>$exception->errors()],422); });
        $exceptions->render(function (ModelNotFoundException $exception, Request $request) { if ($request->is('api/*')) return response()->json(['status'=>404,'message'=>'Resource not found.'],404); });
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) { if ($request->is('api/*')) return response()->json(['status'=>$exception->getStatusCode(),'message'=>$exception->getMessage() ?: 'Request failed.'], $exception->getStatusCode()); });
    })->create();
