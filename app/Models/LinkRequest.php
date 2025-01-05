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

    //Relation Start
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }
}
