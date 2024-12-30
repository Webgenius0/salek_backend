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
        'total_month',
        'additional_charge',
        'introduction_title',
        'cover_photo',
        'class_video',
        'status',
    ];

    // Relation Start
    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    public function reviewCount()
    {
        return $this->reviews()->count();
    }

    public function purchasers()
    {
        return $this->belongsToMany(User::class, 'course_user')
                    ->withPivot('price', 'access_granted', 'purchased_at')
                    ->withTimestamps();
    }

    public function studentLesson()
    {
        return $this->hasMany(Lesson::class);
    }

    public function homework()
    {
        return $this->hasOne(Homework::class);
    }
}
