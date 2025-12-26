<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Enable broadcasting routes for WebSocket authentication
            Route::middleware('api')->group(function () {
                Broadcast::routes();
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'owner' => \App\Http\Middleware\EnsureUserIsOwner::class,
            'tenant' => \App\Http\Middleware\EnsureUserIsTenant::class,
            'approved' => \App\Http\Middleware\EnsureUserIsApproved::class,
            'otp.verified' => \App\Http\Middleware\EnsureOtpIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
