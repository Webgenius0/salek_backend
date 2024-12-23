<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Payment;
use App\Models\Purchase;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentService extends Service
{
    use ApiResponse;

    public function store($user, $courseId, $price)
    {
        $paymentObj = new Payment();

        $paymentObj->payment_id    = Str::random(5);
        $paymentObj->user_id       = $user;
        $paymentObj->item_id       = $courseId;
        $paymentObj->amount        = $price;
        $paymentObj->currency      = 'USD';
        $paymentObj->purchase_type = 'stripe';
        $paymentObj->quantity      = 1;

        $paymentObj->save();
    }

    public function update($userId, $itemId, $paymentType, $paymentMethod)
    {
        try {
            DB::beginTransaction();

            $item = Course::find($itemId);
            if (!$item) {
                return $this->failedResponse('Item not found', 404);
            }

            $payment = Payment::firstOrNew(['item_id' => $itemId, 'user_id' => $userId]);
            $this->savePayment($payment, $userId, $item->price, $paymentType, $paymentMethod);

            $purchase = Purchase::firstOrNew(['user_id' => $userId, 'course_id' => $itemId]);
            $this->savePurchase($purchase, $userId, $itemId, $item->price);

            $courseUser = CourseUser::firstOrNew(['user_id' => $userId, 'course_id' => $itemId]);
            $this->saveCourseUser($courseUser, $userId, $itemId, $item->price);

            DB::commit();
            return $this->successResponse(true, 'Course enrolled successfully', $courseUser, 201);
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedDBResponse('Database Error', $e->getMessage(), 422);
        }
    }

    private function savePayment($payment, $userId, $price, $paymentType, $paymentMethod)
    {
        $payment->user_id        = $userId;
        $payment->payment_id     = Str::random(5);
        $payment->payment_method = $paymentType;
        $payment->amount         = $price;
        $payment->currency       = 'USD';
        $payment->purchase_type  = 'stripe';
        $payment->quantity       = 1;
        $payment->payment_method = $paymentMethod;
        $payment->save();
    }

    private function savePurchase($purchase, $userId, $itemId, $price)
    {
        $purchase->user_id           = $userId;
        $purchase->course_id         = $itemId;
        $purchase->payment_plan      = 'annual';
        $purchase->amount_paid       = $price;
        $purchase->payment_date      = now();
        $purchase->next_payment_date = now();
        $purchase->save();
    }

    private function saveCourseUser($courseUser, $userId, $itemId, $price)
    {
        $courseUser->user_id        = $userId;
        $courseUser->course_id      = $itemId;
        $courseUser->price          = $price;
        $courseUser->access_granted = true;
        $courseUser->purchased_at   = now();
        $courseUser->save();
    }

}
