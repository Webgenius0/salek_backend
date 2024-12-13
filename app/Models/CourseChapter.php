<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseChapter extends Model
{
    protected $fillable = [
        'course_id',
        'name',
    ];
}
