<?php

namespace App\Services;

use App\Events\MessageSend;
use App\Models\Message;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class MessageService extends Service
{
    use ApiResponse;
    
    public function store($data)
    {
        $data['sender_id'] = Auth::id();
        
        $message = Message::create($data);
        broadcast(new MessageSend($data));
        return $this->successResponse(true, 'Message created successfully', $message, 201);
    }
}
