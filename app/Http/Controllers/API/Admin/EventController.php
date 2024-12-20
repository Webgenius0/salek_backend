<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Models\Event;
use App\Services\EventService;
use App\Traits\ApiResponse;

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
        $query = Event::with(['category:id,name'])
            ->select('id', 'title', 'thumbnail', 'event_location', 'category_id', 'status');
        
        if ($type !== 'all') {
            $query->where('status', $type);
        }

        $events = $query->get();

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
            (int) $total_seat
        );
    }

    public function show(Event $event = null)
    {
        if(!$event || $event->created_by !== request()->user()->id){
            return $this->failedResponse('Event not found or You are not authorized to view this event.', 404);
        }

        $data = [
            'event_id'    => $event->id,
            'event_title' => $event->title,
            'event_slug'  => $event->slug,
            'description' => $event->description,
            'category'    => $event->category->name,
            'price'       => $event->price,
            'thumbnail'   => $event->thumbnail,
            'created_by'  => $event->creator->name,
            'status'      => $event->status,
        ];

        return $this->successResponse(true, 'Event details', $data, 200);
    }
}
