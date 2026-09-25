<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Providers\MidtransServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        MidtransServiceProvider::class,
    ])
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/auth.php',
            __DIR__.'/../routes/admin.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->preventRequestForgery(except: [
            'webhook/midtrans/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, Throwable $exception, Request $request) {
            $statusCode = $response->getStatusCode();

            if ($request->expectsJson()) {
                return $response;
            }

            if (app()->hasDebugModeEnabled()) {
                return $response;
            }

            return Inertia::render('Errors/ErrorPage', [
                'status' => $statusCode,
            ])
                ->toResponse($request)
                ->setStatusCode($statusCode);
        });
    })->create();
