<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\Backend\DashboardController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:web', 'onlyAdmin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class,'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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

Route::get('/optimize-clear', function () {
    try {
        Artisan::call('optimize:clear');
        return response()->json([
            'message' => 'Optimize cleared.',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while clearing optimize.',
            'error' => $e->getMessage(),
        ], 500);
    }
});

require __DIR__.'/auth.php';
