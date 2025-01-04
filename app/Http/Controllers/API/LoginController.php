<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\LoginService;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use ApiResponse;
    
    public $loginService;
    
    public function __construct()
    {
        $this->loginService = new LoginService();
    }

    /**
     * main method for login 
     *
     * @param LoginRequest $request
     * @return mixed
    */
    public function store(LoginRequest $request)
    {
        $user = $this->loginService->checkUserVerification($request->input('email'));

        if(!$user){
            return $this->failedAuthResponse('User Not Found', 404);
        }

        if(!$user->is_verified){
            return $this->failedAuthResponse('Your account is not verified yet,please verify first', 422);
        }

        if(!Hash::check($request->input('password'), $user->password)){
            return $this->failedAuthResponse('Your password not matched', 422);
        }

        $credentials = $request->only(['email', 'password']);
        $response    = $this->loginService->authenticate($credentials);

        if (!$response['success']) {
            return response()->json(['error' => $response['message']], 401);
        }        

        return response()->json([
            'status'       => true,
            'message'      => $response['message'],
            'token'        => $response['token'],
            'user'         => $response['user'],
            'is_subscribe' => (bool) $user->hasActiveSubscription(),
        ]);
    }

    /**
     * refresh token method
     *
     * @return mixed
    */
    public function refresh()
    {
        return LoginService::refresh();
    }
}
