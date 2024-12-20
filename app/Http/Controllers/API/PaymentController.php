<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\StripeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreStripeRequest;

class PaymentController extends Controller
{
    public $stripeServiceObj;
    
    public function __construct()
    {
        $this->stripeServiceObj = new StripeService();
    }

    public function create($id)
    {
        try {
            DB::beginTransaction();

            $course = Course::find($id);

            $user = User::find(Auth::id());

            $paymentObj = new Payment();

            $paymentObj->payment_id    = Str::random($length = 10);
            $paymentObj->user_id       = $user->id;
            $paymentObj->item_id       = $course->id;
            $paymentObj->amount        = $course->price;
            $paymentObj->currency      = 'usd';
            $paymentObj->purchase_type = 'stripe';
            $paymentObj->quantity      = 1;

            $paymentObj->save();

            $purchaseObj = new Purchase();

            $purchaseObj->user_id      = $user->id;
            $purchaseObj->course_id    = $course->id;
            $purchaseObj->payment_plan = 'monthly';
            $purchaseObj->amount_paid  = 33;
            $purchaseObj->payment_date = Carbon::now();
            $purchaseObj-> next_payment_date = Carbon::now()->addDays(30);

            $purchaseObj->save();

            $alreadyPurchased = $user->purchasedCourses()->where('course_id', $course->id)->first();

            if ($alreadyPurchased) {
                DB::table('course_user')
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->update([
                        'price' => $course->price,
                        'purchased_at' => Carbon::now(),
                        'access_granted' => true,
                    ]);
            } else {
                $user->purchasedCourses()->attach($course->id, [
                    'price' => $course->price,
                    'purchased_at' => Carbon::now(),
                    'access_granted' => true,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Course purchased successfully!']);

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
    
    public function store(StoreStripeRequest $request, $id)
    {   
        $itemType      = $request->item_type;
        $currency      = $request->currency;
        $itemId        = $request->item_id;
        $paymentType   = $request->payment_type;
        $paymentMethod = $request->payment_method;
        

        if($paymentMethod === 'stripe'){
            return $this->stripeServiceObj->createPayment(
                (string) $itemType, 
                (string) $currency,
                $itemId,
                $id, 
                (string) $paymentType,
                (string) $paymentMethod
            );
        }
    }

    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->updatePaymentStatus($paymentIntent, 'succeeded');
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    $this->updatePaymentStatus($paymentIntent, 'failed');
                    break;

                default:
                    break;
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function updatePaymentStatus($paymentIntent, $status)
    {
        try {
            DB::beginTransaction();

            $metadata = $paymentIntent->metadata;
            $userId   = $metadata->user_id;
            $itemId   = $metadata->item_id;
            $amount   = $metadata->amount;


            if (!$userId || !$itemId) {
                Log::warning('Missing metadata in payment intent', ['metadata' => $metadata]);
                return;
            }

            $paymentInfo = Payment::where('payment_id', $paymentIntent->id)->where('user_id', $userId)->where('item_id', $itemId)->first();

            $paymentInfo->status = $status;
            $res = $paymentInfo->save();

            DB::commit();
            if($res){
                $purchase = Purchase::where('user_id', $userId)->where('course_id', $itemId)->first();
                if(!$purchase){
                    $purchaseObj = new Purchase();

                    $purchaseObj->user_id = $userId;
                    $purchaseObj->course_id = $itemId;
                    $purchaseObj->payment_plan = 'monthly';
                    $purchaseObj->amount_paid = $amount;

                    $purchaseObj->save();
                }

                $purchase->amount_paid = $amount;
                $purchase->save();
            }

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }
}
