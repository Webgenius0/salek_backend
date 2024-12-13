<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterClass extends Model
{
    protected $fillable = [
        'course_id',
        'courses_chapter_id',
        'title',
        'image_url',
        'video_url',
        'duration',
    ];
}
