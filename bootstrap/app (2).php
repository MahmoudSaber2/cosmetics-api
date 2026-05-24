<?php

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'locale' => \App\Http\Middleware\ApiLocale::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class
        ]);
    })
->withExceptions(function (Exceptions $exceptions) {

   $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
        return ApiResponse::error( __('messages.not_found'), [], HttpStatusCode::NOT_FOUND);
    });

    // Catch NotFoundHttpException AFTER Laravel converts it
    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
        return ApiResponse::error( __('messages.not_found'), [], HttpStatusCode::NOT_FOUND);
    });
})->create();
