<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    use ApiResponse;
    
    public function index()
    {

        $instructors = User::with(['courses'])->where('role', 'teacher')->latest()->get();

        $data = $instructors->map(function($instructor){
            return [
                'id'            => $instructor->id,
                'avatar'        => $instructor->avatar,
                'name'          => $instructor->name,
                'total_courses' => $instructor->courses->count(),
            ];
        });

        return $this->successResponse(true, 'Our Instructors', $data, 200);
    }
}
