<?php

use App\Http\Controllers\API\ParentController;
use App\Http\Controllers\API\RequestController;
use App\Http\Controllers\API\SubscriptionController;
use Illuminate\Support\Facades\Route;

//'BEGIN' :- For parent route
Route::middleware(['auth:api', 'onlyParent'])->group(function () {

    Route::controller(ParentController::class)->group(function () {
        Route::prefix('/parent')->name('parent.')->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/student/list', 'show')->name('student');
        });
    });

    Route::controller(RequestController::class)->group(function () {
        Route::prefix('request')->name('request.')->group(function () {
            Route::post('/link/{id}', 'store')->name('link');
            Route::post('/sent', 'sentRequest')->name('sent');
            Route::post('/cancel/{id}', 'cancelRequest')->name('cancel');
        });
    });

    Route::controller(SubscriptionController::class)->group(function () {
        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::post('/parent', 'parentSubscribe')->name('parentsubscribe');
        });
    });
});
//'END' :- For parent route
