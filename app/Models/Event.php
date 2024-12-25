<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'event_date',
        'event_location',
        'price',
        'total_seat',
        'thumbnail',
        'created_by',
        'updated_by',
        'status',
        'flag',
    ];

    // Relation Start
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function eventBook()
    {
        return $this->hasMany(BookEvent::class)->with('user.profile');;
    }
}
