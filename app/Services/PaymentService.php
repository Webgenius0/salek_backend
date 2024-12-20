<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService extends Service
{
    public function store($user, $courseId, $price)
    {
        $paymentObj = new Payment();

        $paymentObj->payment_id = Str::random(5);
        $paymentObj->user_id = $user;
        $paymentObj->item_id = $courseId;
        $paymentObj->amount = $price;
        $paymentObj->currency = 'USD';
        $paymentObj->purchase_type = 'stripe';
        $paymentObj->quantity = 1;

        $paymentObj->save();
    }
}
