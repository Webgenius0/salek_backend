<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'seats',
        'status',
    ];
}
