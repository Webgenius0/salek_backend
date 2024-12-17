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

    public function getKeyName()
    {
        return 'slug';
    }

    // Relation Start
    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class);
    }

}
