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

Route::get('/run-migrate-fresh', function () {
    try {
        $output = Artisan::call('migrate:fresh', ['--seed' => true]);
        return response()->json([
            'message' => 'Migrations executed.',
            'output' => nl2br($output)
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while running migrations.',
            'error' => $e->getMessage(),
        ], 500);
    }
});
