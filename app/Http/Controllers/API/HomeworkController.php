<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\QuestionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHomeworkRequest;
use App\Models\Course;

class HomeworkController extends Controller
{
    public $questionServiceObj;

    public function __construct()
    {
        $this->questionServiceObj = new QuestionService();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(StoreHomeworkRequest $request)
    {
        $course_id     = $request->input('course_id');
        $chapter_id    = $request->input('chapter_id');
        $lesson_id     = $request->input('lesson_id');
        $title         = $request->input('title');
        $instruction   = $request->input('instruction');
        $link          = $request->input('link');
        $deadline      = $request->input('deadline');
        $type          = $request->input('type');
        $question_type = $request->input('question_type');

        $user   = request()->user();
        $course = Course::find($course_id);

        if(!$course){
            return response()->json(['message' => 'Course not found'], 404);
        }

        if($course->created_by !== $user->id){
            return response()->json(['message' => 'You are not authorized to create homework for this course'], 403);
        }

        $file = null;

        if($question_type === 'files'){
            if(empty($request->file('file'))){
                return response()->json(['message' => 'File is required'], 400);
            }

            if($request->hasFile('file')){
                $file = $request->file('file');
            }
        }else{
            if(!$link){
                return response()->json(['message' => 'Link is required']);
            }
        }

        return $this->questionServiceObj->store($course_id, $chapter_id, $lesson_id, $title, $instruction, $file, $link, $deadline, $type,$question_type);
    }
}
