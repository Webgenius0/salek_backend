<?php

use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\Admin\CourseController;
use App\Http\Controllers\API\Admin\EventController;
use App\Http\Controllers\API\HomeworkController;
use App\Http\Controllers\API\InstructorController;
use App\Http\Controllers\API\SettingController;
use App\Models\Event;
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
            Route::get('/details/{id}', 'show')->name('details');
            Route::get('/details/short/{id}', 'showShort');
            Route::post('/chpater/store', 'chapterStore')->name('chapter');
            Route::post('/lesson/store', 'lessonStoreTwo')->name('lesson');
            Route::post('/lesson/next/store', 'lessonStore');
            Route::get('/list', 'courseList')->name('all');
            Route::get('/wise/chapter/{id}', 'courseWiseChapter')->name('wisechapter');
            Route::get('chapter/wise/lesson/{course_id}/{chapter_id}', 'courseChapterWiseLession')->name('wiselession');
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
        Route::post('/events/{id}/mark-completed', 'markAsCompleted');

        // Event Booking Overview
        Route::get('/booking/overview', 'bookingOverview')->name('booking-overview');

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

    Route::controller(EventController::class)->group(function () {
        Route::prefix('event')->name('event.')->group(function () {
            Route::get('teacher/popular', 'popularEvent');
        });
    });
    Route::controller(CourseController::class)->group(function () {
        Route::prefix('course')->name('course.')->group(function () {
            Route::get('teacher/popular', 'popularCourse');
        });
    });
    Route::controller(CourseController::class)->group(function () {
        Route::prefix('course')->name('course.')->group(function () {
            Route::get('/lessons/{course_id}/{chapter_id}/{lesson_id}', 'getLesson');

        });
    });


});
//'END' :- For admin route
