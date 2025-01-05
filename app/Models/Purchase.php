<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{

    protected $fillable = [
        'user_id',
        'course_id',
        'payment_plan',
        'amount_paid',
        'payment_date',
        'next_payment_date',
    ];
}
