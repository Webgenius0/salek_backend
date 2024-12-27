<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'comment',
    ];

    // Relation Start
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'user_id', 'user_id');
    }

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function reactions()
    {
        return $this->hasMany(ReviewReact::class);
    }
}
