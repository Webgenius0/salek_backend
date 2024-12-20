<?php

namespace App\Services;

use App\Models\Payment;

class SubscribeService extends Service
{
    public function paymentService($id, $price)
    {
        $paymentObj = new Payment();

        $paymentObj->user_id = $id;
        $paymentObj->payment_id = 'sub_'.uniqid();
        $paymentObj->item_id = rand(1000, 9999);
        $paymentObj->amount = $price;
        $paymentObj->currency = 'USD';
        $paymentObj->purchase_type = 'subscription';
        $paymentObj->quantity = 1;

        $paymentObj->save();
    }
}
