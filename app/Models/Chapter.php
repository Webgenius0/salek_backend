<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'course_id',
        'name',
        'slug',
        'level_label',
        'chapter_order',
    ];

    //Relation Start
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
