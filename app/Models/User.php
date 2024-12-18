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
        return $this->belongsToMany(Course::class, 'course_user')
                    ->withPivot('price', 'access_granted', 'purchased_at')
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
}
