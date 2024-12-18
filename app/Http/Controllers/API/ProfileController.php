<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    use ApiResponse;
    
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


            $user->name = $name;
            $user->email = $email;

            $user->save();

            DB::commit();
            
            return $this->successResponse(true, 'Update your information successfully', $user, 200);

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return response()->json(['status' => false, 'message' => 'Database error', $e->getMessage(), 422]);
        }
    }
}
