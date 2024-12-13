<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Str;
use App\Services\FileService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EventService extends Service
{
    use ApiResponse;
    
    public $eventObj, $fileServiceObj;

    public function __construct()
    {
        $this->eventObj = new Event();
        $this->fileServiceObj = new FileService();
    }

    public function store(

        int $creatorId,
        string $title,
        string $description,
        int $category_id,
        $event_date,
        string $event_location,
        int $price,
        $thumbnail
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
            $this->eventObj->created_by     = $creatorId;
            $this->eventObj->status         = 'upcoming';
            $this->eventObj->created_at     = Carbon::now();

            $res = $this->eventObj->save();
            
            DB::commit();
            if($res){
                return $this->successResponse(true, 'Event created successfully', $this->eventObj, 201);
            }

            return $this->failedResponse('Event create failed', 400);

        } catch (\Exception $e) {
            DB::commit();
            info($e);
            return $this->failedResponse('Event create failed', 400);
        }
    }
}
