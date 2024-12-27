<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Services\MessageService;

class MessageController extends Controller
{
    public $messageServiceObj;

    public function __construct()
    {
        $this->messageServiceObj = new MessageService();
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
