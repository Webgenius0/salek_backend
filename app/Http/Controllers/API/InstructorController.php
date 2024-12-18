<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Payment;
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

    public function dashboard()
    {
        $allStudents = User::where('role', 'student')->get();

        $newUsers = $allStudents->filter(function($student){
            return $student->created_at >= now()->subDays(7);
        });

        $totalRevenue = Payment::sum('amount');

        $events = Event::where('status', 'complete')->orWhere('status', 'upcoming')->get();

        $totalBooking = $events->filter(function($event){
            return $event->status === 'complete';
        });

        $upcomingEvents = $events->filter(function($event){
            return $event->status === 'upcoming';
        });
        
        $data = [
            'student_overview' => [
                'new_student' => count($newUsers),
                'total_student' => count($allStudents),
                'total_revenue' => $totalRevenue,
            ],

            'event_overview' => [
                'total_booking' => count($totalBooking),
                'upcoming_event' => count($upcomingEvents),
                'total_revenue' => $totalRevenue,
            ],
        ];

        return $data;
    }

    public function show($id)
    {
        $teacher = User::where('id', $id)->where('role', 'teacher')->first();

        if(!$teacher){
            return $this->failedResponse('Instructor not found', 404);
        }
    }
}
