<?php

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
            'locale' => \App\Http\Middleware\SetLocale::class,
            'api.locale' => \App\Http\Middleware\ApiLocale::class,
        ]);

        // Apply locale middleware to web routes
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Apply API locale middleware to API routes
        $middleware->api(append: [
            \App\Http\Middleware\ApiLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
