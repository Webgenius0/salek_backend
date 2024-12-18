<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'created_by',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
