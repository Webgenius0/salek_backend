<?php

namespace App\Services;

use Stripe\Stripe;
use App\Models\Payment;
use Stripe\PaymentIntent;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StripeService extends Service
{
    use ApiResponse;

    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
    
    /**
     * Create a payment intent and record the payment in the database.
     *
     * @param string $currency The currency in which the payment is made.
     * @param mixed $item_id The ID of the item being purchased.
     * @param string $paymentType The type of payment (e.g., 'monthly' or 'one-time').
     * @param string $paymentMethod The method of payment (e.g., 'card').
     *
     * @return \Illuminate\Http\JsonResponse A success response with the payment intent client secret.
    */
    public function createPayment(string $currency, $total, $itemId, string $paymentType, string $paymentMethod)
    {
        try {
            DB::beginTransaction();

            $paymentIntent = PaymentIntent::create([
                'amount'   => $total * 100,
                'currency' => $currency,
                'metadata' => [
                    'user_id'       => Auth::id(),
                    'item_id'       => $itemId,
                    'amount'        => $total,
                    'currency'      => $currency,
                    'quantity'      => 1,
                    'paymentMethod' => $paymentMethod,
                    'purchase_type' => $paymentType,
                ],
            ]);

            Payment::create([
                'payment_id'       => $paymentIntent->id,
                'user_id'          => Auth::id(),
                'item_id'          => $itemId,
                'amount'           => $total,
                'currency'         => $currency,
                'quantity'         => 1,
                'metadata'         => $paymentIntent['paymentIntent'],
                'transaction_date' => now(),
                'payment_method'   => $paymentMethod,
                'status'           => 'pending',
            ]);

            DB::commit();

            return $this->successResponse(true, 'Payment Intent created successfully.', $paymentIntent->client_secret, 200);
            
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedDBResponse('Database error', $e->getMessage(), 422);
        }
    }
}
