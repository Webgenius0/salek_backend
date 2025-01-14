<?php

namespace App\Traits;

trait ApiResponse
{
    public function authResponse($status, $message, $data, $code)
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
            'code'    => $code
        ]);
    }

    public function failedAuthResponse($message, $code)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => [],
            'code'    => $code
        ], $code);
    }

    public function successResponse($status, $message, $data, $code)
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
            'code'    => $code
        ]);
    }

    public function failedResponse($message, $code )
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => [],
            'code'    => $code
        ], $code);
    }

    public function failedDBResponse($message, $data, $code)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => $data,
            'code'    => $code
        ], $code);
    }

    public function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }

    public function error($data, $message = null, $code = 404)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }
}
