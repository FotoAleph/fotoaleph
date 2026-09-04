<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveTenantConnection;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'tenant.connection' => ResolveTenantConnection::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    // Interceptamos las respuestas de error HTTP
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            // Permitir ver la traza detallada si estás en desarrollo local
            if (app()->environment(['local', 'testing'])) {
                return null; // Deja que Laravel maneje el error normalmente con Ignition
            }

            $status = $e->getStatusCode();

            // Si es un código 403, 404, 500, etc., renderizamos el componente Vue con Inertia
            if (in_array($status, [403, 404, 500, 503])) {
                return Inertia::render('Error', [
                    'status' => $status,
                ])
                ->toResponse($request)
                ->setStatusCode($status);
            }
        });
    })->create();
