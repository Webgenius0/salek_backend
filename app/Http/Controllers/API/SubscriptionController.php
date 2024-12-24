<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\SubscribeService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SubscriptionStoreRequest;
use App\Traits\ApiResponse;

class SubscriptionController extends Controller
{
    use ApiResponse;
    
    public $subscriptionServiceObj;
    
    public function __construct()
    {
        $this->subscriptionServiceObj = new SubscribeService();
    }
    
    /**
     * this method use for store subscription
     * only use student
     *
     * @param SubscriptionStoreRequest $request
     * @return mixed
    */
    public function store(SubscriptionStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::find(Auth::id());
        
            $type = $request->input('type');

            $subscription = Subscription::where('user_id', $user->id)->first();
            
            $startDate = Carbon::now();
            $endDate   = match ($type) {
                'monthly'   => $startDate->copy()->addDays(30),
                'quarterly' => $startDate->copy()->addDays(90),
                'annual'    => $startDate->copy()->addYear(),
            };

            $price = match ($type) {
                'monthly'   => MONTHLY_SUBSCRIPTION,
                'quarterly' => MONTHLY_SUBSCRIPTION * 3 * (1 - QUARTERLY_DISCOUNT / 100),
                'annual'    => MONTHLY_SUBSCRIPTION * 12 * (1 - ANNUAL_DISCOUNT / 100),
            };

            $this->subscriptionServiceObj->paymentService($user->id, $price);
            
            if($subscription){
                $previousDate = Carbon::parse($subscription->ends_at);
                $daysDifference = $startDate->diffInDays($previousDate);
                
                $newEndDate = $daysDifference > 1 ? $previousDate->addDays($daysDifference) : $previousDate;
                $daysFromNow = floor(Carbon::now()->diffInDays($newEndDate));
                
                $subscription->update([
                    'type'         => $type,
                    'start_date'   => $startDate,
                    'ends_at'      => $newEndDate,
                    'quantity'     => $daysFromNow,
                    'stripe_price' => $price,
                ]);
                DB::commit();
                return response()->json([
                    'message'      => 'Subscription updated successfully',
                    'subscription' => $subscription
                ]);
            }

            $subscription = Subscription::create([
                'user_id'       => $user->id,
                'stripe_id'     => 'sub_'.uniqid(),
                'type'          => $type,
                'quantity'      => floor($startDate->diffInDays($endDate)),
                'start_date'    => $startDate,
                'ends_at'       => $endDate,
                'stripe_status' => 'active',
                'stripe_price'  => $price
            ]);

            DB::commit();

            return response()->json([
                'message'      => 'Subscription created successfully',
                'subscription' => $subscription
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            info($e);

            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * this method use for parent subscribe
     * only use parent
     *
     * @return mixed
    */
    public function parentSubscribe(SubscriptionStoreRequest $request)
    {
        $user = User::find(Auth::id());

        $type = $request->type;

        $student = $user->linkRequests->first();

        if($student){
            $subscription = Subscription::where('user_id', $student->student_id)->first();

            $startDate = Carbon::now();
            $endDate   = match ($type) {
                'monthly'   => $startDate->copy()->addDays(30),
                'quarterly' => $startDate->copy()->addDays(90),
                'annual'    => $startDate->copy()->addYear(),
            };

            $price = match ($type) {
                'monthly'   => MONTHLY_SUBSCRIPTION,
                'quarterly' => MONTHLY_SUBSCRIPTION * 3 * (1 - QUARTERLY_DISCOUNT / 100),
                'annual'    => MONTHLY_SUBSCRIPTION * 12 * (1 - ANNUAL_DISCOUNT / 100),
            };

            $this->subscriptionServiceObj->paymentService($user->id, $price);

            if($subscription){
                $previousDate = Carbon::parse($subscription->ends_at);
                $daysDifference = $startDate->diffInDays($previousDate);
                
                $newEndDate = $daysDifference > 1 ? $previousDate->addDays($daysDifference) : $previousDate;
                $daysFromNow = floor(Carbon::now()->diffInDays($newEndDate));
                
                $subscription->update([
                    'type'         => $type,
                    'start_date'   => $startDate,
                    'ends_at'      => $newEndDate,
                    'quantity'     => $daysFromNow,
                    'stripe_price' => $price,
                ]);
                DB::commit();
                return response()->json([
                    'message'      => 'Subscription updated successfully',
                    'subscription' => $subscription
                ]);
            }

            $subscription = Subscription::create([
                'user_id'       => $user->id,
                'stripe_id'     => 'sub_'.uniqid(),
                'type'          => $type,
                'quantity'      => floor($startDate->diffInDays($endDate)),
                'start_date'    => $startDate,
                'ends_at'       => $endDate,
                'stripe_status' => 'active',
                'stripe_price'  => $price
            ]);

            DB::commit();

            return response()->json([
                'message'      => 'Subscription created successfully',
                'subscription' => $subscription
            ]);
        }

        return $this->failedResponse('You have no student right now.', 404);
    }
}
