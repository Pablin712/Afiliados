<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'internal_api_token' => \App\Http\Middleware\VerifyInternalApiToken::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\AuditLogMiddleware::class,
            \App\Http\Middleware\EnsureUserPhoneIsSet::class,
            \App\Http\Middleware\EnsureSingleActiveSession::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\AuditLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Temporary switch: by default use Laravel's native exception rendering.
            // Set APP_USE_CUSTOM_ERROR_VIEW=true to enable the custom generic error page again.
            if (! (bool) env('APP_USE_CUSTOM_ERROR_VIEW', false)) {
                return null;
            }

            // Keep detailed traces only when debug mode is enabled.
            if (config('app.debug')) {
                return null;
            }

            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.error_generic'),
                ], $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500);
            }

            if (View::exists('errors.generic')) {
                return response()->view('errors.generic', [
                    'statusCode' => $statusCode,
                ], $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500);
            }

            return response(
                __('messages.error_generic').' (HTTP '.$statusCode.')',
                $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500
            );
        });
    })->create();
