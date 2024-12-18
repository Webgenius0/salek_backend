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
    
    public function store($id)
    {
        try {
            DB::beginTransaction();

            $parent = Auth::user();
        
            $studentExists = User::where('id', $id)
                ->where('role', 'student')
                ->exists();

            if (!$studentExists) {
                return $this->failedResponse('Student not found', 404);
            }

            $linkRequestExists = LinkRequest::where('parent_id', $parent->id)
                ->where('student_id', $id)
                ->exists();

            if ($linkRequestExists) {
                return $this->failedResponse('You already sent a link request to the student', 400);
            }
            
            $data = LinkRequest::create([
                'student_id' => $id,
                'parent_id'  => $parent->id,
                'status'     => 'request',
            ]);

            DB::commit();
            return $this->successResponse(true, 'Request sent to the student', $data, 201);
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedResponse('Database error : ', $e->getMessage(), 422);
        }
    }

    /**
     * Cancel Link Request Method
     *
     * @param [string] $id
     * @return mixed
    */
    public function cancelRequest($id)
    {
        $parent = Auth::user();

        $linkRequest = LinkRequest::where('student_id', $id)
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
