<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
            'forgot-password',
            'reset-password',
            'logout'
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Cette fonction définit QUAND Laravel doit répondre en JSON
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            // Si la requête commence par api/ on force le JSON
            if ($request->is('api/*')) {
                return true;
            }

            // Sinon, on laisse Laravel décider (ex: si le header Accept est présent)
            return $request->expectsJson();
        });
    })->create();
