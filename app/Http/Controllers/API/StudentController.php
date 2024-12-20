<?php

namespace App\Http\Controllers\API;

use App\Models\LinkRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    use ApiResponse;
    
    /**
     * this method use for displaying student dashboard
     * course and event are displaying
     * also displaying the course perfomance
     *
     * @return mixed
    */
    public function index()
    {
        $user = Auth::user();
        
        $data = [
            'id' => $user->id,
            'name' =>'Hey, ' . $user->name,
            'welcome_msg' => 'Lets start learning',
        ];

        return $data;
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
}
