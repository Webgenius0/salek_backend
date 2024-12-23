<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHomework extends Model
{
    protected $fillable = [
        'user_id',
        'homework_id',
        'answer_script',
        'score',
        'comment',
        'submission_at',
    ];
}
