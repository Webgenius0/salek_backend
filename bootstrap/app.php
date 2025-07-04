<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware(['web'])->prefix('admin')->group(base_path('routes/admin.php'));
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/api/teacher.php'));
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/api/student.php'));
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/api/parent.php'));
            Route::middleware(['api'])->prefix('api')->name('api.')->group(base_path('routes/api/global.php'));
        }
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:api']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'onlyTeacher' => \App\Http\Middleware\CheckTeacher::class,
            'onlyAdmin' => \App\Http\Middleware\CheckAdmin::class,
            'onlyStudent' => \App\Http\Middleware\CheckStudent::class,
            'onlyParent' => \App\Http\Middleware\CheckParent::class,
            'check.parent.student' => \App\Http\Middleware\CheckParentOrStudentRole::class,
            'authCheck' => App\Http\Middleware\AuthCheckMiddleware::class
        ]);

        $middleware->append(\App\Http\Middleware\CustomPostSize::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
