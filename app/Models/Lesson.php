<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    
    protected $fillable = [
        'chapter_id',
        'course_id',
        'name',
        'lesson_order',
        'image_url',
        'video_url',
        'duration',
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

}
