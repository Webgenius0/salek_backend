<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseUser extends Model
{
    protected $table = "course_user";

    protected $fillable = [
        'user_id',
        'course_id',
        'price',
        'access_granted',
        'purchased_at',
    ];
}
