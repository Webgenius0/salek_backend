<?php

namespace App\Services;

use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Auth;

class StripeService extends Service
{
    public function createPayment(string $itemType, $purchase_type, string $currency, $item_id, $id)
    {
        $itemType = ucfirst(strtolower($itemType));
        $model = app("App\Models\\$itemType");
        $item = $model::where('id', $id)->first();
        
        $paymentIntent = PaymentIntent::create([
            'amount'   => $item->price * 100,
            'currency' => $currency,
            'metadata' => ['purchase_type' => $purchase_type],
        ]);

        $metadata = $paymentIntent->metadata ? json_encode($paymentIntent->metadata) : null;

        $payment = Payment::create([
            'payment_id'       => $paymentIntent->id,
            'amount'           => $item->price,
            'currency'         => $currency,
            'status'           => $paymentIntent->status,
            'user_id'          => Auth::id(),
            'purchase_type'    => $purchase_type,
            'item_id'          => $item_id,
            'quantity'         => 1,
            'transaction_date' => now(),
            'payment_method'   => 'credit_card',
            'metadata'         => $metadata,
            // 'receipt_url'      => $paymentIntent->charges->data[0]->receipt_url,
        ]);

        return response()->json([
            'message'        => 'PaymentIntent created successfully.',
            'payment_intent' => $paymentIntent->id,
            'client_secret'  => $paymentIntent->client_secret,
        ]);
    }
}
