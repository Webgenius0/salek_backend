<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    use ApiResponse;
    
    public function store($id)
    {
        $student = User::where('id', $id)->where('role', 'student')->first();

        if(!$student){
            return $this->failedResponse('Student not found', 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'This feature is now under maintennece.When you start implement this api,Make sure with you developer.Thank you',
            'code' => 400
        ]);
    }
}
