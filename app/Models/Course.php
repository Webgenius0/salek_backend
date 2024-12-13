<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'created_by',
        'category_id',
        'name',
        'slug',
        'description',
        'total_class',
        'price',
        'status',
    ];
}
