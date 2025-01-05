<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookTeacher extends Model
{
    protected $fillable = [
        'teacher_id',
        'booked_id',
        'start_time',
        'end_time',
        'booked_date',
        'status',
    ];

    // Relation Start
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }

    public function profile()
    {
        return $this->hasOneThrough(
            Profile::class,
            User::class,
            'id',
            'user_id',
            'teacher_id',
            'id'
        );
    }
}
