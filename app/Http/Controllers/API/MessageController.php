<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\MessageService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\ReceivedMessageRequest;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public $messageServiceObj;

    public function __construct()
    {
        $this->messageServiceObj = new MessageService();
    }

    /**
     * Handle the incoming request to retrieve messages.
     *
     * @param \App\Http\Requests\ReceivedMessageRequest $request The incoming request instance.
     * @return \Illuminate\Http\Response The response containing the retrieved messages.
    */
    public function index(ReceivedMessageRequest $request)
    {
        $senderId   = Auth::id();
        $receivedId = $request->input('received_id');

        return $this->messageServiceObj->get($senderId, $receivedId);
    }

    /**
     * Store a newly created message in storage.
     *
     * @param StoreMessageRequest $request The request instance containing the validated data.
     * @return \Illuminate\Http\Response The response after storing the message.
    */
    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();
        
        return $this->messageServiceObj->store($validated);
    }
}
