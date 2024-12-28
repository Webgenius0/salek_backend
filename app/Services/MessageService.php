<?php

namespace App\Services;

use App\Events\MessageSend;
use App\Models\Message;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class MessageService extends Service
{
    use ApiResponse;

    /**
     * Retrieve messages between two users.
     *
     * This method fetches messages exchanged between the sender and receiver specified by their IDs.
     * It retrieves messages where the sender is the senderId and the receiver is the receivedId, 
     * or vice versa. If no messages are found, it returns a failed response with a 404 status code.
     * Otherwise, it maps the messages to a structured array and returns a success response with the data.
     *
     * @param int $senderId The ID of the sender.
     * @param int $receivedId The ID of the receiver.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the messages or an error message.
    */
    public function get($senderId, $receivedId)
    {
        $messages = Message::with(['sender', 'receiver'])->where(function ($query) use ($senderId, $receivedId) {
            $query->where('sender_id', $senderId)
                ->where('receiver_id', $receivedId);
        })->orWhere(function ($query) use ($senderId, $receivedId) {
            $query->where('sender_id', $receivedId)
                ->where('receiver_id', $senderId);
        })->orderBy('created_at', 'desc')->get();

        if($messages->isEmpty()):
            return $this->failedResponse('No message found', 404);
        endif;
        
        $data = $messages->map(function($msg){
            return [
                'message_id'    => $msg->id ,
                'sender_id'     => $msg->sender_id ,
                'sender_name'   => $msg->sender->name,
                'receiver_id'   => $msg->receiver_id ,
                'receiver_name' => $msg->receiver->name ,
                'message'       => $msg->message ,
                'time'          => $msg->created_at->diffForHumans()
            ];
        });

        return $this->successResponse(true, 'All messages', $data, 200);
    }
    
    /**
     * Store a newly created message in storage.
     *
     * This method sets the sender_id to the currently authenticated user's ID,
     * creates a new message with the provided data, broadcasts the message send event,
     * and returns a success response.
     *
     * @param array $data The data for creating the message.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success of the operation.
    */
    public function store($data)
    {
        $data['sender_id'] = Auth::id();
        
        $message = Message::create($data);
        
        broadcast(new MessageSend($data));
        
        return $this->successResponse(true, 'Message send successfully', $message, 201);
    }
}
