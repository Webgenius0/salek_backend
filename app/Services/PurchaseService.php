<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Purchase;

class PurchaseService extends Service
{
    public function store($user, $courseId, $price)
    {
        $purchaseObj = new Purchase();

        $purchaseObj->user_id           = $user;
        $purchaseObj->course_id         = $courseId;
        $purchaseObj->payment_plan      = 'monthly';
        $purchaseObj->amount_paid       = $price;
        $purchaseObj->payment_date      = Carbon::now();
        $purchaseObj->next_payment_date = Carbon::now()->addDays(30);

        $purchaseObj->save();
    }
}
