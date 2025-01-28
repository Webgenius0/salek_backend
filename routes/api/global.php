<?php

use App\Http\Controllers\API\Admin\EventController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:api'])->group(function () {
    Route::controller(EventController::class)->prefix('events')->name('event.')->group(function (){
        // We are doing this post to change the changes
        Route::post('/details/{id}', 'show')->name('details');
        Route::get('/list/{type}', 'index')->name('list');
        // Route::get('/popular', 'popularEvent')->name('popular');
    });
});
