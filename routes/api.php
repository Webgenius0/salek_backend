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
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CardController;
use App\Http\Controllers\API\HomeworkController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\ProgressController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\VideoController;

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
            Route::post('/chpater/store', 'chapterStore')->name('chapter');
            Route::post('/lesson/store', 'lessonStore')->name('lesson');
            Route::get('/list', 'courseList')->name('all');
            Route::get('/wise/chapter/{id}', 'courseWiseChapter')->name('wisechapter');
        });
    });

    Route::controller(InstructorController::class)->group(function(){
        Route::prefix('instructor')->name('instructor.')->group(function(){
            Route::get('/dashboard', 'dashboard')->name('dashboard');
        });
    });

    Route::prefix('events')->name('event.')->group(function(){
        Route::controller(EventController::class)->group(function(){
            Route::post('/store', 'store')->name('store');
            Route::get('/details/{id}', 'show')->name('details');
        });
    });

    Route::controller(HomeworkController::class)->group(function(){
        Route::prefix('homework')->name('homework.')->group(function(){
            Route::post('/store', 'store')->name('store');
            Route::post('/update', 'update')->name('update');
        });
    });

    Route::controller(SettingController::class)->group(function(){
        Route::prefix('setting')->name('setting.')->group(function(){
            Route::post('/update', 'update')->name('update');
        });
    });
});
//'END' :- For admin route

//'BEGIN' :- For parent route
Route::middleware(['auth:api', 'onlyParent'])->group(function(){

    Route::controller(ParentController::class)->group(function(){
        Route::prefix('/parent')->name('parent.')->group(function(){
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/student/list', 'show')->name('student');
        });
    });

    Route::controller(RequestController::class)->group(function(){
        Route::prefix('request')->name('request.')->group(function(){
            Route::post('/link/{id}', 'store')->name('link');
            Route::post('/cancel/{id}', 'cancelRequest')->name('cancel');
        });
    });

    Route::controller(SubscriptionController::class)->group(function(){
        Route::prefix('subscription')->name('subscription.')->group(function(){
            Route::post('/parent', 'parentSubscribe')->name('parentsubscribe');
        });
    });
});
//'END' :- For parent route

//'BEGIN' :- For student and parent route
Route::middleware(['auth:api', 'check.parent.student'])->group(function(){

    //For Review Route
    Route::controller(ReviewController::class)->group(function(){
        Route::prefix('review')->name('review.')->group(function(){
            Route::get('/list/{type}/{id}', 'index')->name('list');
            Route::post('/store', 'store')->name('store');
        });

        Route::prefix('react')->name('react.')->group(function(){
            Route::post('/store', 'reactStore')->name('store');
        });
    });
    //For Review Route

    // For Subscription Route
    Route::controller(SubscriptionController::class)->group(function(){
        Route::prefix('subscription')->name('subscription.')->group(function(){
            Route::post('/store', 'store')->name('store');
        });
    });
    // For Subscription Route
    
});
//'END' :- For student and parent route

//'BEGIN' :- For student route
Route::middleware(['auth:api', 'onlyStudent'])->group(function(){
    
    Route::controller(StudentController::class)->group(function(){
        Route::prefix('student')->name('student.')->group(function(){
            Route::get('/request', 'getRequest')->name('request');
            Route::get('/accept/request/{stdId}', 'acceptRequest')->name('accept');
            Route::get('/cancel/request/{stdId}', 'cancelRequest')->name('cancel');
            Route::get('/parent/list', 'show')->name('show');
        });
    });

    //For Video Route
    Route::controller(VideoController::class)->group(function(){
        Route::prefix('video')->name('video.')->group(function(){
            Route::post('/show', 'show')->name('show');
            Route::post('/status/update', 'update')->name('update');
        });
    });
    //For Video Route

    // For Student Progress Route
    Route::controller(ProgressController::class)->group(function(){
        Route::prefix('progress')->name('progress.')->group(function(){
            Route::post('/store', 'store')->name('calculate');
        });
    });
    // For Student Progress Route

    //For Homework route
    Route::controller(HomeworkController::class)->group(function(){
        Route::prefix('homework')->name('homework.')->group(function(){
            Route::post('/submit/work', 'submit')->name('add_homework');
        });
    });
    //For Homework route

    Route::prefix('events')->name('event.')->group(function(){
        Route::controller(EventController::class)->group(function(){
            Route::post('/booking', 'bookEvent')->name('booking');
        });
    });

    Route::controller(PaymentController::class)->group(function(){
        Route::prefix('course')->name('course.')->group(function(){
            Route::post('/pay', 'store')->name('pay.course');
        });
    });

    Route::controller(PaymentController::class)->group(function(){
        Route::prefix('payment')->name('payment.')->group(function(){
            Route::post('/list', 'index')->name('list');
        });
    });

    Route::controller(ProfileController::class)->group(function(){
        Route::prefix('profile')->name('profile.')->group(function(){
            Route::post('/show', 'show')->name('show');
        });
    });

    Route::controller(CourseController::class)->group(function(){
        Route::get('/current/courses', 'currentCourse')->name('current.course');
        Route::get('/course/all/classess/{id}', 'courseWithClass')->name('course.class');
        Route::get('/course/achievement/{id}', 'courseAchievement')->name('achievement');
        Route::get('/course/complete', 'completeCourse')->name('complete');
        Route::get('/course/ongoing', 'ongoingCourse')->name('ongoing');
        Route::get('/course/achieve/all', 'allAchievement')->name('course.achievement');
        Route::get('/course/progress/{id}', 'showProgress')->name('course.progress');
    });

    Route::get('/upcoming/event', [EventController::class, 'upcomingEvent'])->name('upcomig.event');
});
//'END' :- For student route

//'BEGIN' :- For global route
Route::middleware(['auth:api'])->group(function(){

    Route::controller(StudentController::class)->group(function(){
        Route::prefix('student')->name('student.')->group(function(){
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/list', 'allStudent')->name('all_student.list');
        });
    });

    Route::controller(ProfileController::class)->group(function(){
        Route::prefix('profile')->name('profile.')->group(function(){
            Route::post('/update', 'update')->name('update');
            Route::get('/view/{id}', 'view')->name('view');
        });
    });

    Route::controller(InstructorController::class)->group(function(){
        Route::prefix('instructor')->name('instructor.')->group(function(){
            Route::get('/list', 'index')->name('list');
            Route::get('/profile/{id}', 'show')->name('profile');
        });
    });

    // For Course Route
    Route::controller(CourseController::class)->group(function(){
        Route::prefix('course')->name('course.')->group(function(){
            Route::get('/all', 'index')->name('list');
            Route::post('/details/{id}', 'show')->name('details');
            Route::get('/level/{id}', 'level')->name('level');
            Route::get('/popular', 'popularCourse')->name('popular');
        });
    });
    //For Course Route

    Route::prefix('events')->name('event.')->group(function(){
        Route::controller(EventController::class)->group(function(){
            Route::get('/list/{type}', 'index')->name('list');
            Route::get('/popular', 'popularEvent')->name('popular');
        });
    });

    // For Booking a Teacher route
    Route::prefix('call')->name('call.')->group(function(){
        Route::controller(BookingController::class)->group(function(){
            Route::get('/list', 'index')->name('list');
            Route::post('/teacher', 'store')->name('teacher');
            Route::delete('/cancel/{id}', 'destroy')->name('cancel');
        });
    });
    // For Booking a Teacher route

    // For message route
    Route::prefix('message')->name('message.')->group(function(){
        Route::controller(MessageController::class)->group(function(){
            Route::post('/send', 'store')->name('send');
            Route::post('/received', 'index')->name('received');
        });
    });
    // For message route

    //For Card Route
    Route::prefix('card')->name('card.')->group(function(){
        Route::controller(CardController::class)->group(function(){
            Route::get('/list', 'index')->name('list');
            Route::post('/store', 'store')->name('store');
        });
    });
    //For Card Route

    //For Logout controller
    Route::controller(LogoutController::class)->group(function(){
        Route::post('/auth/logout', 'logout')->name('logout.us');
    });
    //For Logout controller
});
//'END' :- For global route

Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook'])->name('stripe.webhook');
