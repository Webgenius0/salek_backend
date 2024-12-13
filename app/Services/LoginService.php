<?php

namespace App\Services;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class LoginService extends Service
{
    /**
     * check user authintication
     *
     * @param [type] $credentials
     * @return mixed
    */
    public function authenticate($credentials)
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'Invalid email or password.',
                'token'   => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => Auth::user(),
        ];
    }

    /**
     * check user  verification
     *
     * @param [string] $email
     * @return mixed
    */
    public function checkUserVerification(string $email)
    {
        $user = User::where('email', $email)->first();
        
        return $user;
    }

    /**
     * refresh token
     * static method
     *
     * @return mixed
    */
    public static function refresh()
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());
        return response()->json(['token' => $newToken]);
    }
}
