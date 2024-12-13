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

    public function index($type)
    {
        $query = Event::with(['category:id,name'])
            ->select('id', 'title', 'thumbnail', 'event_location', 'category_id', 'status')
            ->where('created_by', request()->user()->id);

        if ($type !== 'all') {
            $query->where('status', $type);
        }

        $events = $query->get();

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
            $thumbnail
        );
    }

    public function show(Event $event)
    {
        if ($event->created_by !== request()->user()->id) {
            return $this->failedResponse('You are not authorized to view this event.', 400);
        }

        $data = [
            'event_id'    => $event->id,
            'event_title' => $event->title,
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
