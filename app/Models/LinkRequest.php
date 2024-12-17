<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkRequest extends Model
{
    protected $fillable = [
        'student_id',
        'parent_id',
        'status',
    ];
}
