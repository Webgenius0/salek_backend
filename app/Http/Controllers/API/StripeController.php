<?php

namespace App\Http\Controllers\API;

use Stripe\Stripe;
use App\Models\Course;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreStripeRequest;

class StripeController extends Controller
{
    public function store(StoreStripeRequest $request, $id)
    {
        $itemType = $request->item_type;
        $itemType = ucfirst(strtolower($itemType));
        $model    = app("App\Models\\$itemType");
        $item     = $model::where('id', $id)->first();
        
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = PaymentIntent::create([
            'amount'   => $item->price * 100,
            'currency' => $request->currency,
            'metadata' => ['purchase_type' => $request->purchase_type],
        ]);

        $metadata = $paymentIntent->metadata ? json_encode($paymentIntent->metadata) : null;

        $payment = Payment::create([
            'payment_id'       => $paymentIntent->id,
            'amount'           => $item->price,
            'currency'         => $request->currency,
            'status'           => $paymentIntent->status,
            'user_id'          => Auth::id(),
            'purchase_type'    => $request->purchase_type,
            'item_id'          => $request->item_id,
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
