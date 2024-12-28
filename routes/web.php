<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function(){
    Route::get('/', [TestController::class, 'welcome'])->name('welcome');
    Route::get('/create/login', [TestController::class, 'create'])->name('login');
    Route::post('/login', [TestController::class, 'login'])->name('login.store');
    Route::get('/register', [TestController::class, 'register'])->name('register');
    Route::post('/register/store', [TestController::class, 'registerStore'])->name('register.store');
});

Route::middleware(['auth'])->group(function(){
    Route::get('/dashboard', [TestController::class, 'dashboard'])->name('dashboard');
    Route::get('/logout', [TestController::class, 'logout'])->name('logout');
});

// Route::get('/clear', function(){
//     Artisan::call('optimize:clear');
//     return response()->json(['message' => 'Optimize clear successfully']);
// })->name('optimize.clear');

// Route::get('/migrate', function(){
//     Artisan::call('migrate');
//     return response()->json(['message' => 'Migrations executed successfully'], 200);
// })->name('db.migrate');

// Route::get('/clear-cache', function () {
//     Artisan::call('cache:clear');
//     Artisan::call('config:clear');
//     Artisan::call('route:clear');
//     Artisan::call('view:clear');

//     return response()->json(['message' => 'All caches cleared successfully'], 200);
// })->name('cache.clear');

// Route::get('/migrate-fresh', function () {
//     if (app()->environment('local')) {
//         Artisan::call('migrate:fresh --seed');

//         return response()->json(['message' => 'Database refreshed and seeded successfully'], 200);
//     }

//     return response()->json(['message' => 'This action is not allowed in this environment'], 403);
// })->name('db.migrate.fresh');
