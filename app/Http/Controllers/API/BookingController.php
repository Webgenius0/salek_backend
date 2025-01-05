<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\BookingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;

class BookingController extends Controller
{
    public $bookingServiceObj;
    
    public function __construct()
    {
        $this->bookingServiceObj = new BookingService();
    }

    /**
     * Display a listing of the booking teachers.
     *
     * This method retrieves a list of teachers booked by the authenticated user.
     * It fetches the booking details along with the associated teacher's profile.
     * The data is then transformed to include specific attributes and returned
     * as a success response.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function index()
    {
        return $this->bookingServiceObj->index();
    }

    /**
     * Store a newly created teacher booking in storage.
     *
     * @param  \App\Http\Requests\StoreTeacherRequest  $request
     * @return \Illuminate\Http\Response
     *
     * This method processes the incoming request to store a new teacher booking.
     * It extracts the teacher ID, booking date, and time range from the request,
     * formats the booking date to the start of the day, and then passes the data
     * to the booking service to handle the actual storage.
    */
    public function store(StoreTeacherRequest $request)
    {
        $data = [
            'teacher_id'  => $request->input('teacher_id'),
            'booked_date' => Carbon::parse($request->input('booking_date'))->startOfDay(),
            'time'        => $request->input('time_range'),
        ];
        
        return $this->bookingServiceObj->store($data);
    }

    /**
     * Destroy the specified booking.
     *
     * This method deletes a booking record identified by its ID.
     *
     * @param int $id The ID of the booking to be deleted.
     * @return mixed The result of the deletion operation from the booking service.
    */
    public function destroy($id)
    {
        return $this->bookingServiceObj->destroy($id);
    }
}
