<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AuthService extends Service
{
    use ApiResponse;
    
    public $userObj;

    /**
     * class construct
     * define user model
    */
    public function __construct()
    {
        $this->userObj = new User();
    }

    /**
     * user data store method
     *
     * @param string $name
     * @param string $email
     * @param string $role
     * @param string $password
     * @return mixed
    */
    public function store(string $name, string $email, string $role, string $password)
    {
        try {
            DB::beginTransaction();

            $this->userObj->name        = $name;
            $this->userObj->email       = $email;
            $this->userObj->password    = Hash::make($password);
            $this->userObj->is_verified = false;
            $this->userObj->role        = $role;

            $res = $this->userObj->save();

            $token = JWTAuth::fromUser($this->userObj);

            $otp = $this->generateOTP();
            $this->sendRegistrationInfo($otp, $this->userObj);

            $data = [
                'id'    => $this->userObj->id,
                'name'  => $this->userObj->email,
                'otp'   => $otp,
                'token' => $token
            ];

            DB::commit();

            if($res){
                return $this->authResponse(true, 'User Registration Successfully', $data, 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return response()->json([
                'success' => false,
                'error' => 'Database Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * verify account method
     *
     * @param [string] $otp
     * @return mixed
    */
    public function verifyAccount($otp)
    {
        try {
            DB::beginTransaction();

            $user = User::where('otp', $otp)->first();
            if(!$user){
                return $this->failedAuthResponse('User not found', 404);
            }

            $currentTime = now();
            $otpExpireTime = $user->otp_expire_at;

            if ($currentTime->greaterThan($otpExpireTime)) {
                return $this->failedAuthResponse('OTP has expired. Please request a new one', 400);
            }

            $user->otp           = null;
            $user->otp_expire_at = null;
            $user->is_verified   = true;
            
            $res = $user->save();
            
            DB::commit();

            if($res){
                $data = [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ];
                return $this->successResponse(true, 'Your account has been verified', $data, 200);
            }

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }

    public function forget($email)
    {
        $user = User::where('email', $email)->first();

        if(!$user){
            return $this->failedAuthResponse('User not found', 404);
        }

        if($user->is_verified == false){
            return $this->failedAuthResponse('You are not verified yet, Please verify first', 400);
        }

        $otp = $this->generateOTP();
        $this->sendRegistrationInfo($otp, $user);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => hash('sha256', $otp),
                'created_at' => Carbon::now()
            ]
        );

        $data = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'otp'   => $otp
        ];

        return $this->successResponse(true, 'User found', $data, 200);
    }

    public function updatePassword($user, $password)
    {
        try {
            DB::beginTransaction();

            $user->otp           = null;
            $user->otp_expire_at = null;
            $user->is_verified   = true;
            $user->password      = Hash::make($password);

            $res = $user->save();

            DB::commit();
            if($res){
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
                $data = [
                    'name'  => $user->name,
                    'email' => $user->email,
                ];

                return $this->authResponse(true, 'Password update successfully', $data, 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }
}
