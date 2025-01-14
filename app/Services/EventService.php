<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\BookEvent;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EventService extends Service
{
    use ApiResponse;

    public $eventObj, $fileServiceObj;

    public function __construct()
    {
        $this->eventObj = new Event();
        $this->fileServiceObj = new FileService();
    }

    /**
     * this is upcoming event method
     * comes from Eventcontroller
     * service class method
     *
     * @return mixed
    */
    public static function upcomingEvent()
    {
        $upcomingEvents = Event::with(['category', 'creator'])->where('status', 'upcoming')->latest()->get();

        if($upcomingEvents->isEmpty()){
            return response()->json([
                'status' => false,
                'message' => 'Event not found',
                'code' =>  404
            ]);
        }

        $events = $upcomingEvents->map(function($event){
            return [
                'event_id'          => $event->id,
                'event_title'       => $event->title,
                'category'          => $event->category->name,
                'event_description' => $event->description,
                'event_date'        => $event->event_date,
                'event_location'    => $event->event_location,
                'price'             => $event->price,
                'thumbnail'         => $event->thumbnail,
                'creator'           => $event->creator->name,
                'status'            => $event->status,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Upcoming Event',
            'data'    => $events,
            'code'    => 200
        ]);
    }

    /**
     * Store a new event in the database.
     *
     * @param int $creatorId The ID of the user creating the event.
     * @param string $title The title of the event.
     * @param string $description A description of the event.
     * @param int $category_id The ID of the category to which the event belongs.
     * @param mixed $event_date The date and time of the event.
     * @param string $event_location The location where the event will take place.
     * @param int $price The price of the event.
     * @param mixed $thumbnail The thumbnail image for the event.
     * @param int $total_seat The total number of seats available for the event.
     * @param string $link A link related to the event.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure.
    */
    public function store(

        int $creatorId,
        string $title,
        string $description,
        int $category_id,
        $event_date,
        string $event_location,
        int $price,
        $thumbnail,
        int $total_seat,
        string $link
    )
    {
        try {
            DB::beginTransaction();

            if($thumbnail != null){
                $thumbnailName             = time() . '.' . $thumbnail->getClientOriginalExtension();
                $filePath                  = $this->fileServiceObj->fileUpload($thumbnail, 'event/thumbnail', $thumbnailName);
                $this->eventObj->thumbnail = $filePath;
            }

            $this->eventObj->title          = Str::title($title);
            $this->eventObj->slug           = Str::slug($title, '-');
            $this->eventObj->description    = $description;
            $this->eventObj->category_id    = $category_id;
            $this->eventObj->event_date     = $event_date;
            $this->eventObj->event_location = $event_location;
            $this->eventObj->price          = $price;
            $this->eventObj->event_link     = $link;
            $this->eventObj->total_seat     = $total_seat;
            $this->eventObj->created_by     = $creatorId;
            $this->eventObj->status         = 'upcoming';
            $this->eventObj->created_at     = Carbon::now();

            $res = $this->eventObj->save();

            DB::commit();
            if($res){

                $data = [
                    'course_id'   => $this->eventObj->id,
                    'course_name' => $this->eventObj->title,
                    'cover_photo' => $this->eventObj->thumbnail,
                    'created_at'  => $this->eventObj->created_at,
                ];

                $this->notifyUsers($data);

                return $this->successResponse(true, 'Event created successfully', $this->eventObj, 201);
            }

            return $this->failedResponse('Event create failed', 400);

        } catch (\Exception $e) {
            DB::commit();
            info($e);
            return $this->failedResponse('Event create failed', 400);
        }
    }

    /**
     * Book an event for the authenticated user.
     *
     * This method allows a user to book a seat for a specified event. It performs several checks:
     * - Verifies if the event exists.
     * - Checks if the user has already booked the event.
     * - Ensures there are enough available seats for the event.
     * - Determines if the user has an active subscription.
     *
     * Depending on the user's subscription status, the booking status will be either 'accept' or 'pending'.
     * The total number of available seats for the event is decremented by the number of seats booked.
     *
     * @param int $eventId The ID of the event to be booked.
     * @param int $seat The number of seats to book.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the booking operation.
    */
    public function bookEvent($eventId, $seat)
    {
        $user      = User::find(Auth::id());
        $event     = Event::find($eventId);

        if(!$event):
            return $this->failedResponse('Event not found', 404);
        endif;

        if (BookEvent::where('user_id', $user->id)->where('event_id', $eventId)->exists()):
            return $this->failedResponse('You already booked this event.', 403);
        endif;

        if ($event->total_seat < $seat):
            return $this->failedResponse('Not enough seats available for this event.', 403);
        endif;

        $userSubscription = $user->hasActiveSubscription();

        if($userSubscription):
            $bookEventObj = new BookEvent([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'booking_code'  => generateRandomString(),
                'seats' => $seat,
                'status' => 'accept',
            ]);

            $bookEventObj->save();

            $event->decrement('total_seat', $seat);

            return $this->successResponse(true, "Event booked successfully", $bookEventObj, 201);
        endif;

        $bookEventObj = new BookEvent([
            'user_id'  => $user->id,
            'event_id' => $event->id,
            'seats'    => $seat,
            'status'   => 'pending',
        ]);

        $bookEventObj->save();

        $event->decrement('total_seat', $seat);

        return $this->successResponse(true, "Event booked successfully", $bookEventObj, 201);
    }
}
