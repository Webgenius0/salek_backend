<?php

use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\Admin\CourseController;
use App\Http\Controllers\API\Admin\EventController;
use App\Http\Controllers\API\HomeworkController;
use App\Http\Controllers\API\InstructorController;
use App\Http\Controllers\API\SettingController;
use Illuminate\Support\Facades\Route;

//'BEGIN' :- For admin route
Route::middleware(['auth:api', 'onlyTeacher'])->group(function () {
    Route::prefix('category')->name('category.')->group(function () {
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/list', 'index')->name('list');
            Route::post('/store', 'store')->name('store');
            Route::get('/show/{id}', 'show')->name('show');
            Route::post('/update', 'update')->name('update');
        });
    });

    Route::controller(CourseController::class)->group(function () {
        Route::prefix('course')->name('course.')->group(function () {
            Route::post('/store', 'store')->name('store');
            Route::post('/chpater/store', 'chapterStore')->name('chapter');
            Route::post('/lesson/store', 'lessonStore')->name('lesson');
            Route::get('/list', 'courseList')->name('all');
            Route::get('/wise/chapter/{id}', 'courseWiseChapter')->name('wisechapter');
            Route::post('/publish', 'publish')->name('publish');
        });
    });

    Route::controller(InstructorController::class)->group(function () {
        Route::prefix('instructor')->name('instructor.')->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/student/profile/{id}', 'studentProfile')->name('student_profile');
            Route::get('/student/courses/{id}', 'studentCourses')->name('student_courses');
        });
    });

    Route::controller(EventController::class)->prefix('events')->name('event.')->group(function () {
        Route::post('/store', 'store')->name('store');
    });

    Route::controller(HomeworkController::class)->group(function () {
        Route::prefix('homework')->name('homework.')->group(function () {
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
            Route::post('/check', 'check')->name('check');
        });
    });

    Route::controller(SettingController::class)->group(function () {
        Route::prefix('setting')->name('setting.')->group(function () {
            Route::post('/update', 'update')->name('update');
        });
    });
});
//'END' :- For admin route
