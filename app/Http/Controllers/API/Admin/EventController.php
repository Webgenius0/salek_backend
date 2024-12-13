<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Services\EventService;

class EventController extends Controller
{
    public $eventServiceObj;

    public function __construct()
    {
        $this->eventServiceObj = new EventService();    
    }


    public function store(EventStoreRequest $request)
    {
        return 1;
    }
}
