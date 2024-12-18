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
        $itemType     = $request->item_type;
        $purchaseType = $request->purchase_type;
        $currency     = $request->currency;
        $itemId       = $request->item_id;
        
        return $this->stripeServiceObj->createPayment((string) $itemType, $purchaseType, (string) $currency,$itemId, $id);
    }
}
