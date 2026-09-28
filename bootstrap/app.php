<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SetAppLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', SetAppLocale::class])->group(base_path('routes/app.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureRole::class]);
        $middleware->redirectGuestsTo(fn () => route('app.login'));
        $middleware->redirectUsersTo(fn () => route('app.dashboard'));
        // Chargily posts server to server, it has no CSRF token; the HMAC signature authenticates it.
        $middleware->validateCsrfTokens(except: ['webhooks/chargily']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
