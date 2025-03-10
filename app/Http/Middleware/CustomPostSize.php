<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\PostTooLargeException;

class CustomPostSize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set a higher post max size (100MB)
        if ($request->server('CONTENT_LENGTH') > 104857600) { // 100MB
            throw new PostTooLargeException;
        }

        return $next($request);
    }
}
