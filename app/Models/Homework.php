<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    protected $table = 'homework';
    
    protected $fillable = [
        'course_id',
        'chapter_id',
        'lesson_id',
        'title',
        'slug',
        'instruction',
        'file',
        'link',
        'deadline',
        'type',
        'status'
    ];
}
