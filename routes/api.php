<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogoutController;
use App\Http\Controllers\API\ParentController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\RequestController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\InstructorController;
use App\Http\Controllers\API\Admin\EventController;
use App\Http\Controllers\API\Admin\CourseController;
use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\CardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('guest')->group(function () {
    Route::controller(AuthController::class)->group(function(){
        Route::post('/user/registration', 'store')->name('user.registration');
        Route::post('/verify/account', 'verify')->name('verify.account');
        Route::post('/forget/password', 'forgetPassword')->name('forget.password');
        Route::post('/update/password', 'updatePassword')->name('update.password');
    });

    Route::controller(LoginController::class)->group(function(){
        Route::post('/auth/login', 'store')->name('login.us');
    });
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
            Route::get('/list', 'index')->name('list');
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
        });
    });

    Route::controller(CourseController::class)->group(function(){
        Route::prefix('course')->name('course.')->group(function(){
            Route::post('/store', 'store')->name('store');
        });
    });

    Route::controller(InstructorController::class)->group(function(){
        Route::prefix('instructor')->name('instructor.')->group(function(){
            Route::get('/dashboard', 'dashboard')->name('dashboard');
        });
    });

    Route::prefix('events')->name('event.')->group(function(){
        Route::controller(EventController::class)->group(function(){
            Route::get('/list/{type}', 'index')->name('list');
            Route::post('/store', 'store')->name('store');
            Route::post('/details/{event}', 'show')->name('details');
        });
    });

    Route::prefix('card')->name('card.')->group(function(){
        Route::controller(CardController::class)->group(function(){
            Route::post('/store', 'store')->name('store');
        });
    });
});
//'END' :- For admin route

//'BEGIN' :- For parent route
Route::middleware(['auth:api', 'onlyParent'])->group(function(){

    Route::controller(ParentController::class)->group(function(){
        Route::prefix('/parent')->name('parent.')->group(function(){
            Route::get('/dashboard', 'index')->name('dashboard');
        });
    });

    Route::controller(RequestController::class)->group(function(){
        Route::prefix('request')->name('request.')->group(function(){
            Route::post('/link/{id}', 'store')->name('link');
            Route::post('/cancel/{id}', 'cancelRequest')->name('cancel');
        });
    });
});
//'END' :- For parent route

//'BEGIN' :- For student route
Route::middleware(['auth:api', 'onlyStudent'])->group(function(){
    
    Route::controller(StudentController::class)->group(function(){
        Route::prefix('student')->name('student.')->group(function(){
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/request', 'getRequest')->name('request');
            Route::get('/accept/request/{stdId}', 'acceptRequest')->name('accept');
            Route::get('/cancel/request/{stdId}', 'cancelRequest')->name('cancel');
        });
    });

    Route::controller(ReviewController::class)->group(function(){
        Route::prefix('review')->name('review.')->group(function(){
            Route::post('/store', 'store')->name('store');
        });
    });

    Route::controller(InstructorController::class)->group(function(){
        Route::prefix('instructor')->name('instructor.')->group(function(){
            Route::get('/list', 'index')->name('list');
        });
    });

    Route::controller(PaymentController::class)->group(function(){
        Route::prefix('course')->name('course.')->group(function(){
            Route::get('/enroll/{id}', 'create')->name('enroll');
            Route::post('/pay/{id}', 'store')->name('pay.course');
        });
    });

    Route::get('/upcoming/event', [EventController::class, 'upcomingEvent'])->name('upcomig.event');
    Route::get('/current/courses', [CourseController::class, 'currentCourse'])->name('current.course');
});
//'END' :- For student route

//'BEGIN' :- For global route
Route::middleware(['auth:api'])->group(function(){

    Route::controller(ProfileController::class)->group(function(){
        Route::prefix('profile')->name('profile.')->group(function(){
            Route::post('/update', 'update')->name('update');
        });
    });

    Route::controller(InstructorController::class)->group(function(){
        Route::prefix('instructor')->name('instructor.')->group(function(){
            Route::get('/profile/{id}', 'show')->name('profile');
        });
    });

    Route::controller(CourseController::class)->group(function(){
        Route::prefix('course')->name('course.')->group(function(){
            Route::get('/all', 'index')->name('list');
            Route::post('/details/{id}', 'show')->name('details');
            Route::get('/popular', 'popularCourse')->name('popular');
        });
    });

    Route::prefix('events')->name('event.')->group(function(){
        Route::controller(EventController::class)->group(function(){
            Route::get('/popular', 'popularEvent')->name('popular');
        });
    });

    Route::prefix('card')->name('card.')->group(function(){
        Route::controller(CardController::class)->group(function(){
            Route::get('/list', 'index')->name('list');
        });
    });

    Route::controller(LogoutController::class)->group(function(){
        Route::post('/auth/logout', 'logout')->name('logout.us');
    });
});
//'END' :- For global route

Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook'])->name('stripe.webhook');
