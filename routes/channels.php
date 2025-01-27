<?php
use App\Events\MessageSent;
use App\Models\ChatGroup;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('message-sent.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

Broadcast::channel('chat.{conversation_id}', function ($user, $conversation_id) {
    return true;
    $conversation = ChatGroup::find($conversation_id);
    return (int) $user->id === (int) $conversation?->user_one_id || (int) $user->id === (int) $conversation?->user_two_id;
});
