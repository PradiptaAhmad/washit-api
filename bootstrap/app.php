<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckScopePassport;
use App\Http\Middleware\ForceJsonResponse;
use ErlandMuchasaj\LaravelGzip\Middleware\GzipEncodeResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    GzipEncodeResponse::class;
    $middleware->api(
        prepend: [
            ForceJsonResponse::class,
        ],
    );
    $middleware->appendToGroup(
        'xendit-callback',
        [
            \App\Http\Middleware\XenditCallbackToken::class,
        ],
    );
    $middleware->alias([
        'scopes' => CheckScopes::class,
        'scope' => CheckScopePassport::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
