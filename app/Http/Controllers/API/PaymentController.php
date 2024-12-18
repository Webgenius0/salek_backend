<?php

namespace App\Http\Controllers\API;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStripeRequest;
use App\Services\StripeService;

class PaymentController extends Controller
{
    public $stripeServiceObj;
    
    public function __construct()
    {
        $this->stripeServiceObj = new StripeService();
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
    
    public function store(StoreStripeRequest $request, $id)
    {   
        $itemType = $request->item_type;
        
        return $this->stripeServiceObj->createPayment((string) $itemType, $request->purchase_type, (string) $request->currency, $request->item_id, $id);
    }
}
