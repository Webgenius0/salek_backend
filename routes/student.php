<?php

use App\Http\Controllers\API\Admin\CourseController;
use App\Http\Controllers\API\Admin\EventController;
use App\Http\Controllers\API\HomeworkController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ProgressController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\VideoController;
use Illuminate\Support\Facades\Route;


//'BEGIN' :- For student route
Route::middleware(['auth:api', 'onlyStudent'])->group(function () {

    Route::controller(StudentController::class)->group(function () {
        Route::prefix('student')->name('student.')->group(function () {
            Route::get('/request', 'getRequest')->name('request');
            Route::get('/accept/request/{rqstId}', 'acceptRequest')->name('accept');
            Route::get('/cancel/request/{rqstId}', 'cancelRequest')->name('cancel');
            Route::get('/parent/list', 'show')->name('show');
        });
    });

    //For Video Route
    Route::controller(VideoController::class)->group(function () {
        Route::prefix('video')->name('video.')->group(function () {
            Route::post('/show', 'show')->name('show');
            Route::post('/status/update', 'update')->name('update');
        });
    });
    //For Video Route

    // For Student Progress Route
    Route::controller(ProgressController::class)->group(function () {
        Route::prefix('progress')->name('progress.')->group(function () {
            Route::post('/store', 'store')->name('calculate');
        });
    });
    // For Student Progress Route

    //For Homework route
    Route::controller(HomeworkController::class)->group(function () {
        Route::prefix('homework')->name('homework.')->group(function () {
            Route::post('/submit/work', 'submit')->name('add_homework');
        });
    });
    //For Homework route

    Route::prefix('events')->name('event.')->group(function () {
        Route::controller(EventController::class)->group(function () {
            Route::post('/booking', 'bookEvent')->name('booking');
        });
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::prefix('course')->name('course.')->group(function () {
            Route::post('/pay', 'store')->name('pay.course');
        });
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::post('/list', 'index')->name('list');
        });
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::post('/show', 'show')->name('show');
        });
    });

    Route::controller(CourseController::class)->group(function () {
        Route::get('/current/courses', 'currentCourse')->name('current.course');
        Route::get('/course/all/classess/{id}', 'courseWithClass')->name('course.class');
        Route::get('/course/achievement/{id}', 'courseAchievement')->name('achievement');
        Route::get('/course/complete', 'completeCourse')->name('complete');
        Route::get('/course/ongoing', 'ongoingCourse')->name('ongoing');
    });

    Route::get('/upcoming/event', [EventController::class, 'upcomingEvent'])->name('upcomig.event');
});
//'END' :- For student route
