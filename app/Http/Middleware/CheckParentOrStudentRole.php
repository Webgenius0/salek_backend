<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class CheckParentOrStudentRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($user && in_array($user->role, ['parent', 'student'])) {
                return $next($request);
            }

            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'message' => 'Unauthorized access. Your session has been terminated.',
            ], Response::HTTP_FORBIDDEN);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token has expired. Please log in again.',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Invalid token. Please log in again.',
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized access. Please log in again.',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
