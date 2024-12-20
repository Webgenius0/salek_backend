<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'stripe_id',
        'type',
        'quantity',
        'ends_at',
        'stripe_status',
        'stripe_price',
    ];
}
