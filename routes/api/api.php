<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogoutController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\InstructorController;
use App\Http\Controllers\API\Admin\CourseController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CardController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\SubscriptionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('guest')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/user/registration', 'store')->name('user.registration');
        Route::post('/verify/account', 'verify')->name('verify.account');
        Route::post('/forget/password', 'forgetPassword')->name('forget.password');
        Route::post('/update/password', 'updatePassword')->name('update.password');
    });

    Route::controller(LoginController::class)->group(function () {
        Route::post('/auth/login', 'store')->name('login.us');
    });
});

Route::middleware(['auth:api'])->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::post('/refresh/token', 'refresh')->name('refresh.token');
    });
});





//'BEGIN' :- For student and parent route
Route::middleware(['auth:api', 'check.parent.student'])->group(function () {

    //For Review Route
    Route::controller(ReviewController::class)->group(function () {
        Route::prefix('review')->name('review.')->group(function () {
            Route::post('/store', 'store')->name('store');
        });

        Route::prefix('react')->name('react.')->group(function () {
            Route::post('/store', 'reactStore')->name('store');
        });
    });
    //For Review Route

    // For Subscription Route
    Route::controller(SubscriptionController::class)->group(function () {
        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::post('/store', 'store')->name('store');
        });
    });
    // For Subscription Route

});
//'END' :- For student and parent route



//'BEGIN' :- For global route
Route::middleware(['auth:api'])->group(function () {

    Route::controller(StudentController::class)->group(function () {
        Route::prefix('student')->name('student.')->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/list', 'allStudent')->name('all_student.list');
        });
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::post('/update', 'update')->name('update');
            Route::get('/view/{id}', 'view')->name('view');
        });
    });

    Route::controller(InstructorController::class)->group(function () {
        Route::prefix('instructor')->name('instructor.')->group(function () {
            Route::get('/list', 'index')->name('list');
            Route::get('/profile/{id}', 'show')->name('profile');
        });
    });

    // For Course Route
    Route::controller(CourseController::class)->group(function () {
        Route::prefix('course')->name('course.')->group(function () {
            Route::get('/all', 'index')->name('list');
            Route::post('/details/{id}', 'show')->name('details');
            Route::get('/level/{id}', 'level')->name('level');
            // Route::get('/popular', 'popularCourse')->name('popular');
        });
    });
    //For Course Route

    // For Booking a Teacher route
    Route::prefix('call')->name('call.')->group(function () {
        Route::controller(BookingController::class)->group(function () {
            Route::get('/list', 'index')->name('list');
            Route::post('/teacher', 'store')->name('teacher');
            Route::delete('/cancel/{id}', 'destroy')->name('cancel');
        });
    });
    // For Booking a Teacher route

    //For Review Route
    Route::controller(ReviewController::class)->group(function () {
        Route::prefix('review')->name('review.')->group(function () {
            Route::get('/list/{type}/{courseId}', 'index')->name('list');
        });
    });
    //For Review Route

    // For message route
    Route::prefix('message')->name('message.')->group(function () {
        Route::controller(MessageController::class)->group(function () {
            Route::post('/send', 'store')->name('send');
            Route::post('/received', 'index')->name('received');
        });
    });
    // For message route

    //For Card Route
    Route::prefix('card')->name('card.')->group(function () {
        Route::controller(CardController::class)->group(function () {
            Route::get('/list', 'index')->name('list');
            Route::post('/store', 'store')->name('store');
        });
    });
    //For Card Route

    //For Notification Route
    Route::controller(NotificationController::class)->group(function () {
        Route::prefix('notification')->name('notification.')->group(function () {
            Route::get('/list', 'index')->name('list');
        });
    });
    //For Notification Route

    //For Logout controller
    Route::controller(LogoutController::class)->group(function () {
        Route::post('/auth/logout', 'logout')->name('logout.us');
    });
    //For Logout controller

    Route::controller(CourseController::class)->group(function () {
        Route::get('/course/achieve/{id}', 'allAchievement')->name('course.achievement');
        Route::get('/course/progress/{id}', 'showProgress')->name('course.progress');
    });
});
//'END' :- For global route

Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook'])->name('stripe.webhook');



// For chat route
Route::get('/chat/get/{user}', [ChatController::class, 'getMessages']);
Route::post('/chat/send/{user}', [ChatController::class, 'sendMessage']);
Route::get('/chat/group/{user}', [ChatController::class, 'getGroup']);
// For chat route
