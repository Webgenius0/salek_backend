<?php

namespace App\Services;

use Stripe\Stripe;
use App\Models\Payment;
use Stripe\PaymentIntent;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class StripeService extends Service
{
    use ApiResponse;

    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
    
    public function createPayment(string $itemType, string $currency, $item_id, $id, string $paymentType, string $paymentMethod)
    {
        $itemType = ucfirst(strtolower($itemType));
        $model    = app("App\Models\\$itemType");
        $item     = $model::where('id', $id)->first();
        
        if ($paymentType === 'monthly') {
            $total = round($item->price / 12);
        } else {
            $total = $item->price;
        }

        $paymentIntent = PaymentIntent::create([
            'amount'   => $total * 100,
            'currency' => $currency,
            'metadata' => [
                'user_id'       => Auth::id(),
                'item_id'       => $item->id,
                'amount'        => $total,
                'currency'      => $currency,
                'quantity'      => 1,
                'paymentMethod' => $paymentMethod,
                'purchase_type' => $paymentType,
            ],
        ]);

        Payment::create([
            'payment_id'     => $paymentIntent->id,
            'user_id'        => Auth::id(),
            'item_id'        => $item->id,
            'amount'         => $total,
            'currency'       => $currency,
            'quantity'       => 1,
            'payment_method' => $paymentMethod,
        ]);

        return $this->successResponse(true, 'Payment Intent created successfully.', $paymentIntent->client_secret, 200);
    }
}
