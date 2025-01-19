<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'homework_id',
        'question',
        'label',
        'answer',

    ];

    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }
}
