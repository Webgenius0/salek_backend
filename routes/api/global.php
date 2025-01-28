<?php

use App\Http\Controllers\API\Admin\EventController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:api'])->group(function () {
    Route::controller(EventController::class)->prefix('events')->name('event.')->group(function (){

        Route::get('/details/{id}', 'show')->name('details');
        // We are doing this post to change the changes
        Route::post('/list/{type}', 'index')->name('list');
        // Route::get('/popular', 'popularEvent')->name('popular');
    });
});
