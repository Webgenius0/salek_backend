<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the user's notifications.
     *
     * This method retrieves the authenticated user's notifications. If the user is not found,
     * it returns a 404 response. If the user has a role of 'teacher' or 'admin', it retrieves
     * the latest notifications from the database and returns them as an array of decoded JSON objects.
     * Otherwise, it returns the user's notifications, including all notifications, unread notifications,
     * and read notifications.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Support\Collection
    */
    public function index()
    {
        $user = User::find(Auth::id());

        if(!$user):
            return $this->failedResponse('User not found', 404);
        endif;

        if($user->role === 'teacher' || $user->role === 'admin'){
            $notifications = DB::table('notifications')
                ->latest()
                ->pluck('data');

            return $notifications->map(function ($item) {
                return json_decode($item, true);
            });
        }

        $notifications = $user->notifications->map(function ($notification) {
            return $notification->data;
        });

        $unreadNotifications = $user->unreadNotifications->map(function ($notification) {
            return $notification->data;
        });

        $readNotifications = $user->readNotifications->map(function ($notification) {
            return $notification->data;
        });

        return response()->json([
            'all_notifications' => $notifications,
            'unread_notifications' => $unreadNotifications,
            'read_notifications' => $readNotifications,
        ]);
    }
}
