<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\EnsureFreshCsrfToken::class,
        ]);
        
        // Register admin middleware
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'verified.user' => \App\Http\Middleware\VerifiedUserMiddleware::class,
            'tier.access' => \App\Http\Middleware\TierAccessMiddleware::class,
            'fresh.csrf' => \App\Http\Middleware\EnsureFreshCsrfToken::class,
        ]);
        
        // Exclude webhook from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'paystack/webhook',
            'monnify/webhook',
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
