<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_verified',
        'otp',
        'otp_expire_at',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relation Start
    public function purchasedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
                    ->withPivot('price', 'access_granted', 'purchased_at')
                    ->withTimestamps();
    }

    public function completedLessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user')
                    ->withPivot('completed', 'completed_at')
                    ->withTimestamps();
    }


    public function courses()
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function linkRequests()
    {
        return $this->hasMany(LinkRequest::class, 'parent_id')->where('status', 'accept');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Get the active subscription for the user.
    */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('ends_at', '>=', now());
    }

    public function hasActiveSubscription()
    {
        return $this->activeSubscription && $this->activeSubscription->stripe_status === 'active';
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function getStudents()
    {
        return User::select('users.*')
            ->join('course_user', 'users.id', '=', 'course_user.user_id')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->where('courses.created_by', $this->id) // $this->id refers to the teacher's ID
            ->distinct()
            ->get();
    }
}
