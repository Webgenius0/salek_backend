<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/admin.php'));
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/teacher.php'));
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/student.php'));
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/parent.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'onlyAdmin' => \App\Http\Middleware\CheckAdmin::class,
            'onlyStudent' => \App\Http\Middleware\CheckStudent::class,
            'onlyParent' => \App\Http\Middleware\CheckParent::class,
            'check.parent.student' => \App\Http\Middleware\CheckParentOrStudentRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
