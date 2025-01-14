<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'class_no',
        'class_name',
        'dob',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
