<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\BookTeacher;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\returnSelf;

class BookingService extends Service
{
    use ApiResponse;
    
    /**
     * Display a listing of the booked teachers for the authenticated user.
     *
     * This method retrieves a list of teachers booked by the authenticated user,
     * including related teacher profiles. The list is sorted by the latest booking.
     * Each booking entry includes details such as booking ID, start and end times,
     * booking date, teacher ID, teacher name, teacher avatar, and booking status.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function index()
    {
        $bookingTeacherList = BookTeacher::with(['teacher.profile'])
        ->where('booked_id', Auth::id())
        ->latest()
        ->get();
        
        $data = $bookingTeacherList->map(function($book){
            return [
                'booking_id'   => $book->id,
                'start_time'   => $book->start_time,
                'end_time'     => $book->end_time,
                'booking_date' => $book->booked_date,
                'teacher_id'   => $book->teacher->id,
                'teacher_name' => $book->teacher->name,
                'avatar'       => $book->teacher->profile->avatar ?? 'files/images/user.png',
                'status'       => $book->status
            ];
        });

        return $this->successResponse(true, 'Booking Teacher list', $data, 200);
    }

    /**
     * Store a new booking for a teacher.
     *
     * This method handles the booking process for a teacher by validating the input data,
     * checking for conflicting bookings, and saving the booking information to the database.
     * It also ensures that the booking is not duplicated for the same date and teacher.
     *
     * @param array $data The booking data, including 'time', 'booked_date', and 'teacher_id'.
     * 
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the booking process.
     * 
     * @throws \Exception If there is an error during the booking process, a database rollback is performed and an error response is returned.
    */
    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            $timeRange = explode(' - ', $data['time']);
            $date      = $data['booked_date'];
            $teacherId = $data['teacher_id'];

            if (count($timeRange) !== 2) {
                return response()->json(['error' => 'Invalid time range format.'], 400);
            }

            $bookTeacher = BookTeacher::where('teacher_id', $teacherId)
                ->where('booked_id', Auth::id())
                ->latest()
                ->first();

            if ($bookTeacher && $bookTeacher->booked_date == $date) {
                return $this->failedResponse('You have already booked this teacher for the selected date.', 400);
            }
            
            if($bookTeacher){
                $this->failedResponse('Your previous booked running still now', 400);
            }

            $conflictingBooking = BookTeacher::where('teacher_id', $teacherId)
            ->where('booked_date', '>', now())
            ->exists();

            if ($conflictingBooking) {
                return $this->failedResponse('The teacher is already booked for this time slot.', 400);
            }

            $startTimeRaw = str_replace('.', ':', $timeRange[0]);
            $endTimeRaw   = str_replace('.', ':', $timeRange[1]);

            $startTime = Carbon::createFromFormat('h:i A', $startTimeRaw);
            $endTime   = Carbon::createFromFormat('h:i A', $endTimeRaw);

            $bookTeacherObj = new BookTeacher();

            $bookTeacherObj->teacher_id  = $teacherId;
            $bookTeacherObj->booked_id   = Auth::id();
            $bookTeacherObj->start_time  = $startTime;
            $bookTeacherObj->end_time    = $endTime;
            $bookTeacherObj->booked_date = $date;
            $bookTeacherObj->status      = 'pending';

            $res = $bookTeacherObj->save();

            DB::commit();
            if($res){
                return $this->successResponse(true, 'You Booked the teacher successfully', $bookTeacherObj, 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedDBResponse('Database error', $e->getMessage(), 422);
        }
    }

    /**
     * Deletes a booking record by its ID.
     *
     * This method attempts to delete a booking record from the database. It begins a database transaction,
     * checks if the booking record exists, and if so, deletes it. If the deletion is successful, it commits
     * the transaction and returns a success response. If the booking record is not found, it returns a 
     * failure response with a 404 status code. If any exception occurs during the process, it rolls back 
     * the transaction and returns a failure response with the exception message.
     *
     * @param int $id The ID of the booking record to be deleted.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the operation.
    */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $bookingTeacher = BookTeacher::where('id', $id)->first();
            if (!$bookingTeacher) {
                return $this->failedResponse('This booking history not found', 404);
            }

            $res = $bookingTeacher->delete();

            DB::commit();
            if ($res) {
                return $this->successResponse(true, 'This booking is successfully canceled', [], 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedDBResponse('Database error', $e->getMessage(), 422);
        }
    }
}
