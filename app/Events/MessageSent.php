<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatMessage $message;

    public function __construct(ChatMessage $message) {
        $this->message = $message;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("chat.{$this->message->conversation_id}")
        ];
    }
    // public function broadcastAs()
    // {
    //     return 'message.sent';
    // }
}

