<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Event;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InstructorController extends Controller
{
    use ApiResponse;
    
    public function index()
    {

        $instructors = User::with(['courses', 'profile'])->where('role', 'teacher')->latest()->get();

        $data = $instructors->map(function($instructor){
            return [
                'id'            => $instructor->id,
                'avatar'        => $instructor->profile->avatar ?? null,
                'subject'       => $instructor->profile->class_name ?? null,
                'name'          => $instructor->name,
                'total_courses' => $instructor->courses->count(),
            ];
        });

        return $this->successResponse(true, 'Our Instructors', $data, 200);
    }

    public function dashboard()
    {
        $allStudents = User::where('role', 'student')->get();

        $newUsers = $allStudents->filter(function($student){
            return $student->created_at >= now()->subDays(7);
        });

        $totalStudentRevenue = Payment::where('status', 'succeeded')->where('purchase_type', 'course')->sum('amount');
        $totalBookingRevenue = Payment::where('status', 'succeeded')->where('purchase_type', 'event')->sum('amount');

        $events = Event::where('status', 'complete')->orWhere('status', 'upcoming')->get();

        $totalBooking = $events->filter(function($event){
            return $event->status === 'complete';
        });

        $upcomingEvents = $events->filter(function($event){
            return $event->status === 'upcoming';
        });
        
        $data = [
            'student_overview' => [
                'new_student'   => count($newUsers),
                'total_student' => count($allStudents),
                'total_revenue' => $totalStudentRevenue,
            ],

            'event_overview' => [
                'total_booking'  => count($totalBooking),
                'upcoming_event' => count($upcomingEvents),
                'total_revenue'  => $totalBookingRevenue,
            ],
        ];

        return $this->successResponse(true, 'Teacher Dashboard', $data, 200);
    }

    public function show($id)
    {
        $teacher = User::where('id', $id)->where('role', 'teacher')->first();

        if(!$teacher){
            return $this->failedResponse('Instructor not found', 404);
        }
    }
}
