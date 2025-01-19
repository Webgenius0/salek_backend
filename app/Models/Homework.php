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
        'deadline',
        'status'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
