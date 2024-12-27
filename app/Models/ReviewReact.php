<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReact extends Model
{
    protected $fillable = [
        'review_id',
        'user_id',
        'total_count',
        'reaction',
    ];

    //Relation Start
    public function react()
    {
        return $this->belongsTo(Review::class, 'review_id', 'id');
    }
}
