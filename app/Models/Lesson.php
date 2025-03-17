<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lesson extends Model
{

    protected $fillable = [
        'chapter_id',
        'course_id',
        'name',
        'lesson_order',
        'video_url',
        'duration',
        'photo'
    ];

    // Relation Start
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'lesson_user')
                    ->withPivot('completed', 'completed_at')
                    ->withTimestamps();
    }

    public function lessonUser()
    {
        return $this->hasMany(LessonUser::class, 'lesson_id', 'id')->where('user_id', Auth::id());
    }


}
