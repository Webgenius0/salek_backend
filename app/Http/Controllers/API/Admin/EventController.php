<?php

namespace App\Http\Controllers\API\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Event;
use App\Models\BookEvent;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\EventService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookEvent;
use App\Http\Requests\EventStoreRequest;

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
        $query = Event::with(['category:id,name', 'eventBook.user.profile'])->select('id', 'title', 'thumbnail', 'event_location', 'category_id', 'status', 'event_date');

        if ($type !== 'all'):
            $query->where('status', $type);
        endif;

        $events = $query->get();

        $events = $events->map(function ($event) {
            return [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_location' => $event->event_location,
                'event_thumbnail' => $event->thumbnail,
                'event_date' => Carbon::parse($event->event_date)->toDateTimeString(),
                'event_status' => $event->status,
                'category' => [
                    'category_id' => $event->category->id,
                    'category_name' => $event->category->name,
                ],
                'attendance' => $event->eventBook->map(function ($book) {
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

    // public function popularEvent()
    // {
    //     $type = 'popular';

    //     $query = Event::with(['category:id,name'])
    //         ->select('id', 'title', 'thumbnail', 'event_location', 'category_id', 'status');


    //     $events = $query->latest()->get();

    //     return $this->successResponse(true, ucfirst($type) . ' Event list', $events, 200);
    // }
    public function popularEvent()
    {
        // Get the authenticated user
        $user = auth('api')->user();

        // Check if the user is authenticated and has the 'teacher' role
        if (!$user || $user->role !== 'teacher') {
            return $this->error('Unauthorized', 403);
        }

        // Fetch popular events created by the authenticated teacher
        $events = Event::with(['category:id,name'])
            ->where('events.created_by', $user->id) // Filter events by the authenticated teacher
            ->select('events.id', 'events.title', 'events.thumbnail', 'events.event_date', 'events.event_location', 'events.category_id', 'events.status')
            ->leftJoin('book_events', 'events.id', '=', 'book_events.event_id') // Join with book_events to calculate popularity
            ->selectRaw('COUNT(book_events.id) as bookings_count')
            ->groupBy('events.id', 'events.title', 'events.thumbnail', 'events.event_date', 'events.event_location', 'events.category_id', 'events.status')
            ->orderByDesc('bookings_count') // Order by popularity
            ->limit(10) // Limit to top 10 events
            ->get();


        if ($events->isEmpty()) {
            return $this->success([], 'No popular events found.', 200);
        }

        return $this->success($events, 'Popular Event list', 200);
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
        if ($request->hasFile('thumbnail')) {
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
        $event = Event::with(['eventBook.user.profile'])->find($id);

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
            'event_date'     => $event->event_date,
            'attendance'     => $event->eventBook->map(function ($book) {
                return [
                    'attendance_id' => $book->user_id,
                    'avatar' => $book->user->profile->avatar ?? null
                ];
            })
        ];

        return $this->successResponse(true, 'Event details', $data, 200);
    }
    public function studentShow($id)
    {
        $event = Event::with(['eventBook.user.profile'])->find($id);

        // Get the authenticated user
        $authenticatedUserId = auth('api')->user()->id;

        // Check if the authenticated user has purchased the event
        $isPurchased = $event->eventBook->contains('user_id', $authenticatedUserId);

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
            'event_date'     => $event->event_date,
            'is_purchased'   => $isPurchased, // Add the purchase status flag
            'attendance'     => $event->eventBook->map(function ($book) {
                return [
                    'attendance_id' => $book->user_id,
                    'avatar' => $book->user->profile->avatar ?? null
                ];
            })
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

    public function markAsCompleted($id)
    {
        // Find the event by ID
        $event = Event::with(['category:id,name', 'eventBook.user.profile'])->find($id);

        // Check if event exists
        if (!$event) {
            return $this->error('Event not found', 404);
        }

        // Toggle the status between "completed" and "upcoming"
        $event->status = $event->status === 'complete' ? 'upcoming' : 'complete';
        $event->save();

        return $this->success($event, 'Event marked as completed successfully.', 200);
    }

    public function bookingOverview()
    {
        $data = DB::table('book_events')
            ->selectRaw('COUNT(book_events.id) as totalBooking,
                        COUNT(CASE WHEN book_events.status = "pending" THEN 1 END) as newBooking,
                        SUM(book_events.amount) as totalRevenue')
            ->first();

        $guests = User::where('role', 'student')->pluck('id');
        // dd($guests);

        $eventGuest = BookEvent::with('user')->whereIn('user_id', $guests)->get()->map(function ($guest) {
            return [
                'avatar'    => $guest->user->profile->avatar,
                'name'      => $guest->user->name,
                'bookId'    => $guest->booking_code,
                'date_book' => Carbon::parse($guest->created_at)->format('dM,Y')
            ];
        });


        return response()->json([
            'status'  => true,
            'message' => 'Data found',
            'data'    => [
                'totalBooking' => $data->totalBooking,
                'newBooking'   => $data->newBooking,
                'totalRevenue' => $data->totalRevenue,
                'guests'       => $eventGuest
            ]
        ]);
    }
}
