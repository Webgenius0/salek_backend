<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
{
    public function create()
    {
        return view('test.auth.login');
    }

    public function register()
    {
        return view('test.auth.signup');
    }
    
    public function registerStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        try {
            DB::beginTransaction();

            User::create([
                'name'     => $request->input('name'),
                'email'    => $request->input('email'),
                'password' => bcrypt($request->input('password')),
            ]);

            DB::commit();

            return redirect()->route('login');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('message', 'Registration failed, please try again.');
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'exists:users,email', 'email'],
            'password' => ['required', 'string', 'min:2'],
        ]);
 
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        $user = User::where('email', $request->input('email'))->where('is_verified', 1)->first();

        if(!$user):
            return redirect()->back()->with('message', 'User not found');
        endif;

        if($user->role !== 'admin'):
            return redirect()->back()->with('message', 'You are not authorized.');
        endif;

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->route('dashboard');
        } else {
            return redirect()->back()->withErrors(['email' => 'Invalid credentials.']);
        }
    }

    public function dashboard()
    {
        $user = User::find(Auth::id());
        $userId = $user->id;
        
        if(!$user->role !== 'admin'):
            return redirect()->with('message', 'You are not authorized');
        endif;
        
        return view('test.dashboard', compact('userId'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->to('/');
    }
}
