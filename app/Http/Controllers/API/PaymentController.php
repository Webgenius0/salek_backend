<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreStripeRequest;
use App\Models\CourseUser;
use App\Models\Subscription;
use App\Services\HelperService;

class PaymentController extends Controller
{
    public $stripeServiceObj,$paymentServiceObj;
    
    public function __construct()
    {
        $this->stripeServiceObj = new StripeService();
        $this->paymentServiceObj = new PaymentService();
    }
    
    /**
     * Store a new payment or update an existing subscription.
     *
     * @param StoreStripeRequest $request The request object containing payment details.
     * @param int $id The ID of the user making the payment.
     * @return mixed The result of the payment processing, which could be a string or a service response.
    */
    public function store(StoreStripeRequest $request)
    {   
        $itemId          = $request->item_id;
        $itemType        = $request->item_type;
        $currency        = $request->currency;
        $paymentType     = $request->payment_type;
        $paymentMethod   = $request->payment_method;
        $subscriptionFee = $request->subscription_fee;
        
        $user = User::find(Auth::id());
        
        // For Subscriptioin payment process
        if($paymentType === 'subscription'):
            $subscription = $user->hasActiveSubscription();
            if($subscription):
                return response()->json(['status' => false, 'message' => "You already subscribed and your validity till " . $subscription->ends_at->toDateString()]);
            endif;

            if(!$subscriptionFee):
                return response()->json(['status' => false, 'message' => 'Please enter subscription fee.']);
            endif;

            return $this->paymentServiceObj->update($user->id, $itemId, $paymentType, $paymentMethod, $itemType, $subscriptionFee);
        endif;
        
        // For monthly payment process
        if($paymentType === 'monthly'):
            $item = HelperService::itemCheck($itemType, $itemId);

            if(!$item):
                return response()->json(['status' => false, 'message' => 'Item not found', 404]);
            endif;
            
            $previousPurchase = Purchase::where('course_id', $item->id)->where('user_id', $user->id)->first();

            if($previousPurchase):
                $validityDate = Carbon::parse($previousPurchase->next_payment_date);
                $now          = Carbon::parse(now());

                if($validityDate->greaterThan($now)):
                    return response()->json([
                        'status' => false,
                        'message' => 'You have already purchased this course. Your validity is until ' . $validityDate->toDateString()
                    ], 400);
                endif;
            endif;
            
            $total = round($item->price / $item->total_month, 2);

            PaymentService::monthlyPurchase($user->id, $itemId, $paymentType, $total);
            HelperService::updateCourseUser($item->id);

            if($paymentMethod === 'stripe'):
                return $this->stripeServiceObj->createPayment(
                    (string) $currency,
                    $total,
                    $itemId,
                    (string) $paymentType,
                    (string) $paymentMethod
                );
            endif;
        endif;

        // For annual payment process
        if($paymentType === 'annual'):
            $item = HelperService::itemCheck($itemType, $itemId);

            if(!$item):
                return response()->json(['status' => false, 'message' => 'Item not found not', 404]);
            endif;
            
            $previousPurchase = Purchase::where('course_id', $item->id)->where('user_id', $user->id)->first();

            if($previousPurchase){
                $validityDate = Carbon::parse($previousPurchase->next_payment_date);
                $now          = Carbon::parse(now());

                if($validityDate->greaterThan($now)):
                    return response()->json([
                        'status' => false,
                        'message' => 'You have already purchased this course. Your validity is until ' . $validityDate->toDateString()
                    ], 400);
                endif;
            }

            PaymentService::annualPurchase($user->id, $itemId, $paymentType, $item->price);
            HelperService::updateCourseUser($item->id);

            if($paymentMethod === 'stripe'):
                return $this->stripeServiceObj->createPayment(
                    (string) $currency,
                    $item->price,
                    $itemId,
                    (string) $paymentType,
                    (string) $paymentMethod
                );
            endif;
        endif;
    }

    /**
     * Handle Stripe webhook events.
     *
     * This method processes incoming webhook events from Stripe. It verifies the
     * event using the Stripe secret key and processes the event based on its type.
     * Currently, it handles the following event types:
     * - payment_intent.succeeded: Updates the payment status to 'succeeded'.
     * - payment_intent.payment_failed: Updates the payment status to 'failed'.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the webhook payload.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the webhook handling.
    */
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload   = $request->getContent();
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

    /**
     * Updates the payment status and related records in the database.
     *
     * This function updates the payment status based on the provided payment intent and status.
     * It also updates the related purchase and course user records accordingly.
     *
     * @param \Stripe\PaymentIntent $paymentIntent The payment intent object containing metadata.
     * @param string $status The new status of the payment (e.g., 'succeeded', 'failed').
     *
     * @return mixed
     *
     * @throws \Exception If an error occurs during the database transaction.
    */
    private function updatePaymentStatus($paymentIntent, $status)
    {
        try {
            DB::beginTransaction();

            $metadata    = $paymentIntent->metadata;
            $userId      = $metadata->user_id;
            $itemId      = $metadata->item_id;
            $amount      = $metadata->amount;
            $paymentType = $metadata->purchase_type;


            if (!$userId || !$itemId) {
                Log::warning('Missing metadata in payment intent', ['metadata' => $metadata]);
                return;
            }

            $paymentInfo = Payment::where('payment_id', $paymentIntent->id)->where('user_id', $userId)->where('item_id', $itemId)->first();

            $paymentInfo->status = $status;
            $res = $paymentInfo->save();

            DB::commit();
            
            if ($res) {
                $purchase = Purchase::where('user_id', $userId)->where('course_id', $itemId)->first();
                if ($purchase) {
                    $purchase->status = $status === 'succeeded' ? 'complete' : 'cancel';
                    $purchase->amount_paid = $amount;
                    $purchase->save();

                    $courseUser = CourseUser::where('user_id', $userId)->where('course_id', $itemId)->first();

                    if ($courseUser) {
                        if ($status === 'succeeded') {
                            $courseUser->access_granted = 1;
                            $courseUser->save();
                        } elseif ($status === 'failed') {
                            $courseUser->delete();
                        }
                    }

                    if($paymentType === 'subscription'){
                        $subscription = Subscription::where('user_id', $userId)->first();
                        if($subscription):
                            $subscription->stripe_status = 'complete';
                            $subscription->save();
                        endif;
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }
}
