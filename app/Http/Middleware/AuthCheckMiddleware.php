<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthCheckMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            
            switch (Auth::user()->role) {
                case 'admin':
                    return redirect()->route('dashboard');
                case 'trainer':
                    abort(403, 'Unauthorized action.');
                case 'client':
                    abort(403, 'Unauthorized action.');
            }
        }
        return $next($request);
    }
}

