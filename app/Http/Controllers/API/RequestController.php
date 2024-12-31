<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\LinkRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    use ApiResponse;
    
    /**
     * Store a link request for a student.
     *
     * This method creates a link request from the authenticated parent to a student.
     * It first checks if the student exists and if a link request has already been sent.
     * If the student exists and no link request has been sent, it creates a new link request.
     * The method uses a database transaction to ensure data integrity.
     *
     * @param int $stdId The ID of the student to link to.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure.
    */
    public function store($stdId)
    {
        try {
            DB::beginTransaction();

            $parent = Auth::user();
        
            $studentExists = User::where('id', $stdId)
                ->where('role', 'student')
                ->exists();

            if (!$studentExists) {
                return $this->failedResponse('Student not found', 404);
            }

            $linkRequestExists = LinkRequest::where('parent_id', $parent->id)
                ->where('student_id', $stdId)
                ->exists();

            if ($linkRequestExists) {
                return $this->failedResponse('You already sent a link request to the student', 400);
            }

            $linkRequestObj = new LinkRequest();

            $linkRequestObj->student_id = (int) $stdId;
            $linkRequestObj->parent_id  = $parent->id;
            $linkRequestObj->status     = 'request';

            $res = $linkRequestObj->save();

            $data = [
                'request_id' => $linkRequestObj->id,
                'student_id' => $linkRequestObj->student_id,
                'parent_id'  => $linkRequestObj->parent_id,
                'send_date'  => $linkRequestObj->created_at
            ];

            DB::commit();
            if($res){
                return $this->successResponse(true, 'Request sent to the student', $data, 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedResponse('Database error : ', $e->getMessage(), 422);
        }
    }

    /**
     * Handles the request to get the list of link requests sent by the authenticated parent user.
     *
     * This method performs the following steps:
     * 1. Retrieves the authenticated user.
     * 2. Checks if the user exists and has the role of 'parent'.
     * 3. If the user is not authorized or not found, returns a failed response with a 404 status code.
     * 4. Retrieves all link requests where the parent_id matches the authenticated user's ID.
     * 5. Maps the retrieved requests to a structured array containing request details.
     * 6. Returns a success response with the list of link requests.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the status, message, and data.
    */
    public function sentRequest()
    {
        $user = User::find(Auth::id());
        if(!$user || !$user->role === 'parent'):
            return $this->failedResponse('Request not found or You are not authorized', 404);
        endif;
        
        $requests = LinkRequest::where('parent_id', $user->id)->get();

        $data = $requests->map(function($rqst){
            return [
                'request_id' => $rqst->id,
                'student_id' => $rqst->student_id,
                'parent_id' => $rqst->parent_id,
                'status' => ($rqst->status === 'request') ? 'pending' : $rqst->status,
                'send_date' => $rqst->created_at
            ];
        });

        return $this->successResponse(true, 'Link request list.', $data, 200);
    }

    /**
     * Cancel a link request.
     *
     * This method cancels a link request identified by the given request ID.
     * It first checks if the authenticated user is the parent associated with the request.
     * If the link request does not exist or does not belong to the parent, it returns a failed response.
     * If the link request has already been accepted, it returns a success response indicating that the parent is already linked with the student.
     * Otherwise, it deletes the link request and returns a success response indicating that the link request was canceled successfully.
     *
     * @param int $rqstId The ID of the link request to be canceled.
     * @return \Illuminate\Http\JsonResponse The response indicating the result of the cancellation.
    */
    public function cancelRequest($rqstId)
    {
        $parent = Auth::user();

        $linkRequest = LinkRequest::where('id', $rqstId)
            ->where('parent_id', $parent->id)
            ->first();

        if (!$linkRequest) {
            return $this->failedResponse('No link request found for this student.', 404);
        }

        if ($linkRequest->status === 'accept') {
            return $this->successResponse(true, 'You are already linked with the student.', $linkRequest, 200);
        }

        $linkRequest->delete();
        
        return $this->successResponse(true, 'Link request canceled successfully.', null, 200);
    }
}
