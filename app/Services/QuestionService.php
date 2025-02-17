<?php

namespace App\Services;

use App\Models\Homework;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionService extends Service
{
    use ApiResponse;

    public $homeworkObj;

    public function __construct()
    {
        $this->homeworkObj = new Homework();
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $course_id
     * @param  int  $chapter_id
     * @param  int  $lesson_id
     * @param  string  $title
     * @param  string  $instruction
     * @param  string  $file
     * @param  string  $link
     * @param  string  $deadline
     * @param  string  $type
     * @param  string  $status
     * @return \Illuminate\Http\Response
    */
    public function store($course_id, $chapter_id, $lesson_id, $title, $instruction, $file, $link, $deadline, $type, $qsType)
    {
        try {
            DB::beginTransaction();

            $this->homeworkObj->course_id  = $course_id;
            $this->homeworkObj->chapter_id = $chapter_id;
            $this->homeworkObj->lesson_id  = $lesson_id;

            if($file != null){
                $path = 'uploads/homework/pdf';
                $path = HelperService::fileUpload($file, $path);

                $this->homeworkObj->file = $path;
            }

            if($qsType === 'links'){
                $this->homeworkObj->link = $link;
            }

            $this->homeworkObj->title       = $title;
            $this->homeworkObj->slug        = Str::slug($title, '-');
            $this->homeworkObj->instruction = $instruction;
            $this->homeworkObj->deadline    = $deadline;
            $this->homeworkObj->type        = $type;
            $this->homeworkObj->status      = 'active';

            $res = $this->homeworkObj->save();

            DB::commit();
            if($res){
                $data = [
                    'homework_id'          => $this->homeworkObj->id,
                    'homework_title'       => $this->homeworkObj->title,
                    'homework_slug'        => $this->homeworkObj->slug,
                    'homework_instruction' => $this->homeworkObj->instruction,
                    'homework_deadline'    => $this->homeworkObj->deadline,
                    'homework_type'        => $this->homeworkObj->type,
                    'homework_status'      => $this->homeworkObj->status,
                ];
                
                return $this->successResponse(true, 'Homework created successfully', $data, 201);
            }

        } catch (\Exception $e) {
            DB::rollback();
            info($e);

            return $this->failedDBResponse('Database error', $e->getMessage(), 500);
        }
    }
}
