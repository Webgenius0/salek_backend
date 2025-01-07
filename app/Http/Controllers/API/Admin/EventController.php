<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\EventService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookEvent;
use App\Http\Requests\EventStoreRequest;
use Carbon\Carbon;

class EventController extends Controller
{
    use ApiResponse;

    public $eventServiceObj;

    public function __construct(Request $request)
    {
        $this->eventServiceObj = new EventService();
    }

    public function index(string $type)
    {
        $query = Event::with(['category:id,name', 'eventBook.user.profile'])->select('id', 'title', 'thumbnail','event_location','category_id', 'status');

        if ($type !== 'all'):
            $query->where('status', $type);
        endif;

        $events = $query->get();

        $events = $events->map(function($event){
            return [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_location' => $event->event_location,
                'event_thumbnail' => $event->thumbnail,
                /* 'event_date' => Carbon::parse($event->event_date)->toDateTimeString(), */
                'event_date' => $event->event_date,
                'event_status' => $event->status,
                'category' => [
                    'category_id' => $event->category->id,
                    'category_name' => $event->category->name,
                ],
                'attendance' => $event->eventBook->map(function($book){
                    return [
                        'attendance_id' => $book->user_id,
                        'avatar' => $book->user->profile->avatar ?? null
                    ];
                })
            ];
        });

        return $this->successResponse(true, ucfirst($type) . ' Event list', $events, 200);
    }

    /**
     * upcoming event method
     * call event service class method
     * static method
     *
     * @return mixed
    */
    public function upcomingEvent()
    {
        return EventService::upcomingEvent();
    }

    public function popularEvent()
    {
        $type = 'popular';

        $query = Event::with(['category:id,name'])
            ->select('id', 'title', 'thumbnail', 'event_location', 'category_id', 'status');


        $events = $query->latest()->get();

        return $this->successResponse(true, ucfirst($type) . ' Event list', $events, 200);
    }

    public function store(EventStoreRequest $request)
    {
        $creatorId      = request()->user()->id;
        $title          = trim($request->input('title'));
        $description    = trim($request->input('description'));
        $category_id    = $request->input('category_id');
        $event_date     = $request->input('event_date');
        $event_location = trim($request->input('event_location'));
        $price          = trim($request->input('price'));
        $total_seat     = trim($request->input('total_seat'));
        $event_link     = trim($request->input('event_link'));

        $thumbnail      = null;
        if($request->hasFile('thumbnail')){
            $thumbnail = $request->file('thumbnail');
        }

        return $this->eventServiceObj->store(
            (int) $creatorId,
            (string) $title,
            (string) $description,
            (int) $category_id,
            $event_date,
            (string) $event_location,
            (int) $price,
            $thumbnail,
            (int) $total_seat,
            (string) $event_link
        );
    }

    /**
     * Display the specified event.
     *
     * @param  int  $id  The ID of the event to display.
     * @return \Illuminate\Http\Response
     *
     * This method retrieves an event by its ID and checks if the event exists and if the current user is authorized to view it.
     * If the event does not exist or the user is not authorized, it returns a failed response with a 404 status code.
     * If the event exists and the user is authorized, it returns a success response with the event details.
    */
    public function show($id)
    {
        $event = Event::find($id);

        if(!$event || $event->created_by !== request()->user()->id){
            return $this->failedResponse('Event not found or You are not authorized to view this event.', 404);
        }

        $data = [
            'event_id'       => $event->id,
            'event_title'    => $event->title,
            'event_slug'     => $event->slug,
            'description'    => $event->description,
            'event_location' => $event->event_location,
            'category'       => $event->category->name,
            'price'          => $event->price,
            'thumbnail'      => $event->thumbnail,
            'created_by'     => $event->creator->name,
            'status'         => $event->status,
            'event_date'     => $event->created_at,
        ];

        return $this->successResponse(true, 'Event details', $data, 200);
    }

    /**
     * Books an event based on the provided request data.
     *
     * @param StoreBookEvent $request The request object containing event booking details.
     * @return mixed The result of the event booking operation.
    */
    public function bookEvent(StoreBookEvent $request)
    {
        $eventId = $request->input('event_id');
        $seat    = $request->input('seat');

        return $this->eventServiceObj->bookEvent($eventId, $seat);
    }
}
