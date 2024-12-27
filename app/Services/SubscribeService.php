<?php

namespace App\Services;

use Stripe\Stripe;
use Carbon\Carbon;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SubscribeService extends Service
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
    
    /**
     * Handle the payment process for a subscription.
     *
     * @param int $id The ID of the user making the payment.
     * @param float $price The amount to be paid.
     * 
     * @return mixed
    */
    public static function paymentService($id, $price)
    {
        try {
            DB::beginTransaction();

            $paymentIntent = PaymentIntent::create([
                'amount'   => $price * 100,
                'currency' => 'usd',
                'metadata' => [
                    'user_id'       => Auth::id(),
                    'amount'        => $price,
                    'currency' => 'usd',
                    'quantity'      => 1,
                    'paymentMethod' => 'stripe',
                    'purchase_type' => 'subscription',
                ],
                'payment_method' => 'stripe',
            ]);

            $paymentObj = new Payment();

            $paymentObj->user_id          = $id;
            $paymentObj->payment_id       = $paymentIntent->id;
            $paymentObj->item_id          = rand(1000, 9999);
            $paymentObj->amount           = $price;
            $paymentObj->currency         = 'USD';
            $paymentObj->metadata         = $paymentIntent['paymentIntent'];
            $paymentObj->transaction_date = now();
            $paymentObj->payment_method   = 'stripe';
            $paymentObj->purchase_type    = 'subscription';
            $paymentObj->quantity         = 1;
            $paymentObj->status           = 'pending';

            $res = $paymentObj->save();

            DB::commit();
            if($res){
                $res = Subscription::where('user_id', $id)->update(['stripe_id' => $paymentIntent->id]);
                if($res){
                    $data = [
                        'status' => true,
                        'client_secret' => $paymentIntent->client_secret
                    ];

                    return $data;
                }
                return $data = [];
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    /**
     * Create a new subscription for the authenticated user.
     *
     * @param float $price The price of the subscription.
     * @param \Carbon\Carbon $endDate The end date of the subscription.
     * @param string $type The type of the subscription.
     * 
     * @return void
    */
    public static function createSubscription($price, $endDate, string $type): void
    {
        DB::beginTransaction();

        try {
            $subscriptionObj = new Subscription();

            $subscriptionObj->user_id       = Auth::id();
            $subscriptionObj->type          = $type;
            $subscriptionObj->stripe_id = Str::random(4);
            $subscriptionObj->stripe_status = 'pending';
            $subscriptionObj->stripe_price  = $price;
            $subscriptionObj->quantity      = floor(Carbon::now()->diffInDays($endDate));
            $subscriptionObj->ends_at       = $endDate;

            $subscriptionObj->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
