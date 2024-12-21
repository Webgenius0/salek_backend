<?php

namespace App\Http\Controllers\API;

use App\Models\Purchase;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Store a newly created resource in public.
     *
     * @param  StorePhotoRequest  $request
    */
    public function store(StorePhotoRequest $request)
    {
        $user = User::find(request()->user()->id);

        if(!$user) {
            return $this->failedResponse('User not found', 404);
        }

        if($request->hasFile('avatar')) {
            $file     = $request->file('avatar');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            
            $profile = Profile::where('user_id', $user->id)->first();

            if($profile) {
                $file->move(public_path('uploads/profile'), $fileName);

                $profile->avatar = 'uploads/profile/' . $fileName;
                $profile->save();
            } else {
                $profile          = new Profile();
                $profile->user_id = $user->id;
                $profile->avatar  = 'uploads/profile/' . $fileName;
                $profile->save();
            }

            return $this->successResponse(true, 'Profile picture uploaded successfully', $profile, 200);
        }
    }
    
    /**
     * Update the specified resource in public.
     *
     * @param  UpdateProfileRequest  $request
    */
    public function update(UpdateProfileRequest $request)
    {
        try {
            DB::beginTransaction();

            $user         = request()->user();
            $name         = $request->input('name');
            $dob          = $request->input('dob');
            $email        = $request->input('email');
            $mobile_phone = $request->input('mobile_phone');
            $gender       = $request->input('gender');
            $class_no     = $request->input('class_no');
            $class_name   = $request->input('class_name');


            $user->name = $name;
            $user->email = $email;

            $res = $user->save();

            DB::commit();
            if($res){
                $profile = Profile::where('user_id', $user->id)->first();

                if(!$profile){
                    $profileObj = new Profile();

                    $profileObj->user_id    = $user->id;
                    $profileObj->dob        = $dob;
                    $profileObj->phone      = $mobile_phone;
                    $profileObj->gender     = $gender;
                    $profileObj->class_no   = $class_no;
                    $profileObj->class_name = $class_name;

                    $profileObj->save();
                }

                if($profile){
                    $profile->dob        = $dob;
                    $profile->phone      = $mobile_phone;
                    $profile->gender     = $gender;
                    $profile->class_no   = $class_no;
                    $profile->class_name = $class_name;

                    $profile->save();
                }

                return $this->successResponse(true, 'Update your information successfully', $user, 200);
            }
            
            return $this->successResponse(true, 'Update your information successfully', $user, 200);

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return response()->json(['status' => false, 'message' => 'Database error', $e->getMessage(), 422]);
        }
    }

    /**
     * Display the specified resource in public.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function show() :JsonResponse
    {
        $user = User::find(Auth::id());
        
        $courseIds = $user->purchasedCourses->pluck('id');

        $purchaseHistory = Purchase::where('user_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->get();
        
        $coursePayments = $purchaseHistory->map(function ($purchase) use ($user) {
            $course = $user->purchasedCourses->firstWhere('id', $purchase->course_id);
                return [
                    'course_id' => $purchase->course_id,
                    'course_title' => $course->name ?? 'Unknown Course',
                    'next_payment_date' => $purchase->next_payment_date,
                ];
        });
        
        $data = [
            'id'                 => $user->id,
            'name'               => $user->name,
            'avatar'             => $user->profile->avatar,
            'class_no'           => $user->profile->class_no,
            'class_name'         => $user->profile->class_name,
            'total_course'       => $user->purchasedCourses->count(),
            'next_payment_dates' => $coursePayments,
        ];

        return $this->successResponse(true, 'Student Course', $data, 200);
    }
}
