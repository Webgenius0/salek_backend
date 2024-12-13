<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyRequest;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\PasswordStoreRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;

class AuthController extends Controller
{
    public $authServiceObj;

    public function __construct()
    {
        $this->authServiceObj = new AuthService();    
    }

    /**
     * user registration method
     *
     * @param RegistrationRequest $request
     * @return mixed
    */
    public function store(RegistrationRequest $request)
    {
        $name     = trim(Str::title($request->input('name')));
        $email    = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');

        return $this->authServiceObj->store($name, $email, $role, $password);
    }

    /**
     * verify account method
     * call the service class
     *
     * @param VerifyRequest $request
     * @return mixed
    */
    public function verify(VerifyRequest $request)
    {
        $otp = trim($request->input('otp'));

        return $this->authServiceObj->verifyAccount($otp);
    }

    /**
     * forget password method
     * check by email
     * @return mixed
    */
    public function forgetPassword(PasswordStoreRequest $request)
    {
        $email = $request->input('email');

        return $this->authServiceObj->forget($email);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $email           = $request->input('email');
        $otp             = $request->input('otp');
        $newPassword     = $request->input('new_password');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !hash_equals($record->token, hash('sha256', $otp))) {
            return response()->json(['error' => 'Invalid or expired token.'], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json(['error' => 'Token has expired.'], 422);
        }

        $user = User::where('email', $email)->first();

        if(!$user){
            return response()->json(['status' => false, 'message' => 'User not found', 404]);
        }

        return $this->authServiceObj->updatePassword($user, $newPassword);
    }
}
