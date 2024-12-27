<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\LinkRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    use ApiResponse;
    
    /**
     * Display the welcome message for the authenticated user.
     *
     * This method retrieves the authenticated user's information and constructs
     * a welcome message. It then returns a success response with the user's ID,
     * a personalized greeting, and a welcome message.
     *
     * @return mixed The success response containing the welcome message data.
    */
    public function index() :mixed
    {
        $user = Auth::user();
        
        $data = [
            'id'          => $user->id,
            'name'        => 'Hey, ' . $user->name,
            'welcome_msg' => ($user->role === 'teacher' || $user->role === 'parent') ? 'Good morning' : 'Lets start learning',
        ];

        return $this->successResponse(true, 'Welcome Message', $data, 200);
    }

    /**
     * Get Request method
     * here only visible which are status request mode
     * only one request are fetched at a time
     *
     * @return mixed
    */
    public function getRequest()
    {
        $user    = Auth::user();
        $request = LinkRequest::with(['parent'])->where('student_id', $user->id)->where('status', 'request')->first();

        if(!$request){
            return $this->failedResponse('Request Not found', 404);
        }
        
        $data = [
            'id'     => $request->parent->id,
            'avatar' => $request->parent->avatar,
            'name'   => $request->parent->name,
        ];
        
        return $this->successResponse(true, 'Request list', $data, 200);
    }

    /**
     * accept parent request method
     * only student can accept this request
     * here pass the parent id for query
     *
     * @param [string] $stdId
     * @return mixed
    */
    public function acceptRequest($parentId)
    {
        $request = LinkRequest::where('parent_id', $parentId)->where('status', 'request')->first();

        if(!$request){
            return $this->failedResponse('Request not found', 404);
        }

        $request->status = 'accept';
        $request->save();

        return $this->successResponse(true, 'Request accepted', $request, 200);
    }

    /**
     * Cancel request method
     * pass the parent id
     * only cancel this request by teacher
     *
     * @param [string] $parentId
     * @return mixed
    */
    public function cancelRequest($parentId)
    {
        $request = LinkRequest::where('parent_id', $parentId)->where('status', 'request')->first();

        if(!$request){
            return $this->failedResponse('Request not found', 404);
        }

        $request->delete();

        return $this->successResponse(true, 'Request canceled', [], 200);
    }

    /**
     * parent list show
     *
     * @return mixed
    */
    public function show()
    {
        $user = Auth::user();
        $requests = LinkRequest::with(['student'])->where('student_id', $user->id)->where('status', 'accept')->get();

        if($requests->isEmpty()){
            return $this->failedResponse('Request not found', 404);
        }

        $data = [
            'id'     => $user->id,
            'name'   => $user->name,
            'avatar' => $user->avatar,
            'count'  => $requests->count(),
            'parent' => [
                'id'     => $requests->first()->parent->id,
                'name'   => $requests->first()->parent->name,
                'avatar' => $requests->first()->parent->avatar,
            ],
        ];

        return $this->successResponse(true, 'Request list', $data, 200);
    }

    /**
     * Retrieve all verified students with their profiles.
     *
     * This method fetches all users with the role of 'student' who are verified,
     * along with their associated profiles. It then maps the student data to include
     * the student's ID, name, and avatar (or a default avatar if not available).
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success status,
     *                                       message, student data, and HTTP status code.
    */
    public function allStudent()
    {
        $students = User::with(['profile'])->where('role', 'student')->where('is_verified', 1)->get();

        $data = $students->map(function($student){
            return [
                'id'     => $student->id,
                'name'   => $student->name,
                'avatar' => $student->profile->avatar ?? 'files/images/user.png',
            ];
        });

        return $this->successResponse(true, 'Student List', $data, 200);
    }
}
