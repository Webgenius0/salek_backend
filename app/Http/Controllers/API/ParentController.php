<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    use ApiResponse;
    
    public function index()
    {
        $user = Auth::user();

        $myStudent = $user->linkRequests->first();
        
        if($myStudent){
            $name = 'Hey, ' . $user->name;
            $welcomeMsg = 'Welcome! See your childs progress';

            $childCourses = Payment::with(['course'])->where('user_id', $myStudent->student_id)->where('purchase_type', 'course')->get();

            $courses = $childCourses->map(function($course){
                return [
                    'course_name' => $course->course->name,
                ];
            });

            $data = [
                'name' => $name,
                'welcome_message' => $welcomeMsg,
                'student_courses' => $courses
            ];

            return $data;
        }else{
            return $this->failedResponse('You have no student right now.', 404);
        }
        
    }

    public function show()
    {
        $user = Auth::user();

        $myStudent = $user->linkRequests->first();

        $student = User::find($myStudent->student_id);

        $data = [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'avatar' => $student->avatar,
        ];

        return $this->successResponse(true, 'Student details', $data, 200);
    }
}
