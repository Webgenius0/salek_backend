<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_id',
        'user_id',
        'item_id',
        'amount',
        'currency',
        'purchase_type',
        'quantity',
        'transaction_date',
        'payment_method',
        'metadata',
        'referral_code',
        'receipt_url',
        'status',
    ];

    // Define relationships if necessary
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'item_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'item_id');
    }
}
