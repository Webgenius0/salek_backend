<?php

use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\Admin\CourseController;
use App\Http\Controllers\API\Admin\EventController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function(){
    Route::post('/user/registration', 'store')->name('user.registration');
    Route::post('/verify/account', 'verify')->name('verify.account');
    Route::post('/forget/password', 'forgetPassword')->name('forget.password');
    Route::post('/update/password', 'updatePassword')->name('update.password');
});

Route::controller(LoginController::class)->group(function(){
    Route::post('/auth/login', 'store')->name('login.us');
});

Route::middleware(['auth:api'])->group(function(){
    Route::controller(LoginController::class)->group(function(){
        Route::post('/refresh/token', 'refresh')->name('refresh.token');
    });
});

//'BEGIN' :- For admin route
Route::middleware(['auth:api', 'onlyAdmin'])->group(function(){
    Route::prefix('category')->name('category.')->group(function(){
        Route::controller(CategoryController::class)->group(function(){
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
        });
    });

    Route::controller(CourseController::class)->group(function(){
        Route::prefix('course')->name('course.')->group(function(){
            Route::post('/store', 'store')->name('store');
        });
    });

    Route::prefix('events')->name('event.')->group(function(){
        Route::controller(EventController::class)->group(function(){
            Route::get('/list/{type}', 'index')->name('list');
            Route::post('/store', 'store')->name('store');
            Route::post('/details/{event}', 'show')->name('details');
        });
    });
});
//'END' :- For admin route

Route::middleware(['auth:api'])->group(function(){
    Route::controller(LogoutController::class)->group(function(){
        Route::post('/auth/logout', 'logout')->name('logout.us');
    });
});
