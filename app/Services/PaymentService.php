<?php

namespace App\Services;

use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\Payment;
use App\Models\Purchase;
use Stripe\PaymentIntent;
use App\Models\CourseUser;
use App\Models\Subscription;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentService extends Service
{
    use ApiResponse;

    /**
     * Updates the payment and subscription details for a user.
     *
     * This function handles the update process for a user's payment and subscription details
     * based on the provided parameters. It performs the following steps:
     * 1. Begins a database transaction.
     * 2. Checks if the specified item exists.
     * 3. Creates or updates the payment record for the user and item.
     * 4. If the payment type is 'stripe', it processes the payment and updates the purchase,
     *    course user, and subscription records.
     * 5. Commits the transaction if all operations are successful.
     * 6. Rolls back the transaction and logs the error if any exception occurs.
     *
     * @param int $userId The ID of the user.
     * @param int $itemId The ID of the item.
     * @param string $paymentType The type of payment (e.g., 'stripe').
     * @param string $paymentMethod The method of payment.
     * @param string $itemType The type of item.
     * @param float $subscriptionFee The subscription fee amount.
     * @return \Illuminate\Http\JsonResponse The response indicating the result of the operation.
    */
    public function update($userId, $itemId, $paymentType, $paymentMethod, $itemType, $subscriptionFee)
    {
        try {
            DB::beginTransaction();

            $item = HelperService::itemCheck($itemType, $itemId);
            if (!$item) {
                return $this->failedResponse('Item not found', 404);
            }
            
            if($paymentMethod === 'stripe'):
                $payment = Payment::firstOrNew(['item_id' => $itemId, 'user_id' => $userId]);
                $secretId = $this->savePayment($payment, $userId, $itemId, $paymentType, $paymentMethod, $subscriptionFee);
                
                $purchase = Purchase::firstOrNew(['user_id' => $userId, 'course_id' => $itemId]);
                $this->savePurchase($purchase, $userId, $itemId, $item->price,$paymentType);
                
                $courseUser = CourseUser::firstOrNew(['user_id' => $userId, 'course_id' => $itemId]);
                $this->saveCourseUser($courseUser, $userId, $itemId, $item->price);

                $userSubscription = Subscription::firstOrNew(['user_id' => $userId]);
                $this->getSubscription($userSubscription, $userId, $subscriptionFee);

                DB::commit();
                return $this->successResponse(true, 'Course enrolled successfully', $secretId, 201);
            endif;
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedDBResponse('Database Error', $e->getMessage(), 422);
        }
    }

    /**
     * Save a payment record and create a payment intent.
     *
     * @param \App\Models\Payment $payment The payment model instance.
     * @param int $userId The ID of the user making the payment.
     * @param int $itemId The ID of the item being purchased.
     * @param float $price The price of the item.
     * @param string $paymentType The type of payment (e.g., 'stripe', 'paypal').
     * @param string $paymentMethod The method of payment (e.g., 'credit_card', 'bank_transfer').
     * @param float $total The total amount to be charged.
     * 
     * @return string The client secret of the created payment intent.
    */
    private function savePayment($payment, $userId, $itemId, $paymentType, $paymentMethod, $total) :string
    {
        try {
            DB::beginTransaction();

            $paymentIntent = PaymentIntent::create([
                'amount'   => $total * 100,
                'currency' => 'usd',
                'metadata' => [
                    'user_id'       => Auth::id(),
                    'item_id'       => $itemId,
                    'amount'        => $total,
                    'currency'      => 'usd',
                    'quantity'      => 1,
                    'paymentMethod' => $paymentMethod,
                    'purchase_type' => $paymentType,
                ],
            ]);
            
            $payment->user_id        = $userId;
            $payment->payment_id     = Str::random(5);
            $payment->payment_method = $paymentType;
            $payment->amount         = $total;
            $payment->currency       = 'USD';
            $payment->purchase_type  = 'stripe';
            $payment->quantity       = 1;
            $payment->payment_method = $paymentMethod;
            $payment->status         = 'pending';
            
            $payment->save();

            DB::commit();
            return $paymentIntent->client_secret;

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }

    /**
     * purchase store function
     *
     * @param [string] $purchase
     * @param [string] $userId
     * @param [string] $itemId
     * @param [string] $price
     * @param [string] $paymentType
     * @return void
    */
    private function savePurchase($purchase, $userId, $itemId, $price, $paymentType) :void
    {
        try {
            DB::beginTransaction();

            $purchase->user_id           = $userId;
            $purchase->course_id         = $itemId;
            $purchase->payment_plan      = $paymentType;
            $purchase->amount_paid       = $price;
            $purchase->payment_date      = now();
            $purchase->next_payment_date = now();
            $purchase->status            = 'pending';

            $purchase->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }

    /**
     * save course to the user function
     *
     * @param [string] $courseUser
     * @param [string] $userId
     * @param [string] $itemId
     * @param [string] $price
     * @return void
    */
    private function saveCourseUser($courseUser, $userId, $itemId, $price) :void
    {
        try {
            DB::beginTransaction();

            $courseUser->user_id        = $userId;
            $courseUser->course_id      = $itemId;
            $courseUser->price          = $price;
            $courseUser->access_granted = 0;
            $courseUser->purchased_at   = now();

            $courseUser->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }

    /**
     * Save subscription details for a user.
     *
     * @param \App\Models\Subscription $subscription The subscription model instance.
     * @param int $userId The ID of the user subscribing.
     * 
     * @return void
    */
    private function getSubscription($subscription, $userId, $price) :void
    {
        try {
            DB::beginTransaction();

            $subscription->user_id       = $userId;
            $subscription->type          = 'subscription';
            $subscription->stripe_id     = Str::random(4);
            $subscription->stripe_price  = $price;
            $subscription->stripe_status = 'pending';
            $subscription->ends_at       = now()->addDays(365);

            $subscription->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }

    /**
     * Handles the annual purchase process for a user.
     *
     * This method creates a new purchase record for a user, sets the payment plan,
     * calculates the next payment date, and saves the purchase details in the database.
     * It uses a database transaction to ensure data integrity.
     *
     * @param int $userId The ID of the user making the purchase.
     * @param int $itemId The ID of the item being purchased.
     * @param string $paymentType The type of payment plan selected.
     *
     * @return void
     *
     * @throws \Exception If an error occurs during the transaction.
    */
    public static function annualPurchase($userId, $itemId, $paymentType, $price) :void
    {
        try {
            DB::beginTransaction();

            $nextPayment = Carbon::now()->addDays(365);

            $purchaseObj = new Purchase();

            $purchaseObj->user_id           = $userId;
            $purchaseObj->course_id         = $itemId;
            $purchaseObj->payment_plan      = $paymentType;
            $purchaseObj->amount_paid       = $price;
            $purchaseObj->payment_date      = now();
            $purchaseObj->next_payment_date = $nextPayment;
            $purchaseObj->status            = 'pending';

            $purchaseObj->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }

    /**
     * Handles the monthly purchase process for a user.
     *
     * This method creates a new purchase record for a user, sets the payment plan,
     * calculates the next payment date, and saves the purchase details in the database.
     * It uses a database transaction to ensure data integrity.
     *
     * @param int $userId The ID of the user making the purchase.
     * @param int $itemId The ID of the item (course) being purchased.
     * @param string $paymentType The type of payment plan selected by the user.
     *
     * @return void
     *
     * @throws \Exception If there is an error during the transaction, it will be caught and logged.
    */
    public static function monthlyPurchase($userId, $itemId, $paymentType, $total) :void
    {
        try {
            DB::beginTransaction();

            $nextPayment = Carbon::now()->addDays(30);

            $purchaseObj = new Purchase();

            $purchaseObj->user_id           = $userId;
            $purchaseObj->course_id         = $itemId;
            $purchaseObj->payment_plan      = $paymentType;
            $purchaseObj->amount_paid       = $total;
            $purchaseObj->payment_date      = now();
            $purchaseObj->next_payment_date = $nextPayment;
            $purchaseObj->status            = 'pending';

            $purchaseObj->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }
}
