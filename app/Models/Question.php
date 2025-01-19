<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }
}
