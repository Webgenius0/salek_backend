<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'course_progress',
        'lesson_progress',
        'homework_progress',
    ];
}
