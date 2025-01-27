<?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    public function index(): JsonResponse
    {
        // Fetch users who are connected as senders or receivers with the authenticated user
        $users = User::whereHas('senders', function ($query) {
            $query->where('receiver_id', Auth::guard('api')->id());
        })->orWhereHas('receivers', function ($query) {
            $query->where('sender_id', Auth::guard('api')->id());
        })->where('id', '!=', Auth::guard('api')->user()->id)->get();

        // Append the last message for each user
        $usersWithMessages = $users->map(function ($user) {
            $lastMessage = ChatMessage::where(function ($query) use ($user) {
                $query->where('sender_id', Auth::guard('api')->id())
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', Auth::guard('api')->id());
            })->latest()->first();

            return [
                'user' => $user,
                'last_message' => $lastMessage,
            ];
        });

        // Sort users by the last message's created_at timestamp in descending order
        $sortedUsersWithMessages = $usersWithMessages->sortByDesc(function ($item) {
            return optional($item['last_message'])->created_at;
        })->values(); // Reset keys after sorting

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Trainers retrieved successfully',
            'data' => $sortedUsersWithMessages,
        ], 200);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $users = User::where('id', '!=', auth('api')->user()->id)->where(function ($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->get();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Trainers retrieved successfully',
            'data'    => $users,
        ], 200);
    }

    public function getMessages(User $user, Request $request): JsonResponse
    {
        $receiver_id = $user->id;
        $sender_id = auth('api')->user()->id;

        $messages = ChatMessage::query()
            ->where(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $sender_id)
                    ->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($receiver_id, $sender_id) {
                $query->where('sender_id', $receiver_id)
                    ->where('receiver_id', $sender_id);
            })
            ->with([
                'sender:id,name,avatar',
                'receiver:id,name,avatar',
            ])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data'    => $messages,
        ]);
    }

    public function sendMessage(User $user, Request $request): JsonResponse
    { 
        $request->validate([
            'message' => 'required|string',
        ]);

        $receiver_id = $user->id;
        $sender_id = auth('api')->user()->id;
        $conversation = ChatGroup::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (!$conversation) {
            $conversation = ChatGroup::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        $message = ChatMessage::create([
            'sender_id'   => $sender_id,
            'receiver_id' => $receiver_id,
            'text'        => $request->message,
            'conversation_id' => $conversation->id,
            'status'      => 'sent',
        ]);

        //* Load the sender's information
        $message->load(['sender:id,name,avatar', 'receiver:id,name,avatar']);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $message,
        ]);

    }

    public function getGroup(User $user)
    {
        $receiver_id = $user->id;
        $sender_id = auth('api')->user()->id;
        $conversation = ChatGroup::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', $sender_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('user_one_id', $sender_id)->where('user_two_id', $receiver_id);
        })->first();

        if (!$conversation) {
            $conversation = ChatGroup::create([
                'user_one_id' => $sender_id,
                'user_two_id' => $receiver_id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Group retrieved successfully',
            'data'    => $conversation,
        ]);
    }

}
