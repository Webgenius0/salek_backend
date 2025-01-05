<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'created_by',
        'type',
        'cardholder_name',
        'card_number',
        'token',
        'expiry_date',
        'cvv',
        'billing_address',
        'status',
    ];
}
