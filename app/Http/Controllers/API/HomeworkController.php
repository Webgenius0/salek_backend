<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Course;
use App\Models\Homework;
use Illuminate\Http\Request;
use App\Services\HomeworkService;
use App\Services\QuestionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreWorkRequest;
use App\Http\Requests\CheckHomeworkRequest;
use App\Http\Requests\StoreHomeworkRequest;
use App\Traits\ApiResponse;

class HomeworkController extends Controller
{
    use ApiResponse;
    // public $questionServiceObj,$homeworkServiceObj;

    // public function __construct()
    // {
    //     $this->questionServiceObj = new QuestionService();
    //     $this->homeworkServiceObj = new HomeworkService();
    // }

    // /**
    //  * Store a newly created homework in storage.
    //  *
    //  * @param  \App\Http\Requests\StoreHomeworkRequest  $request
    //  * @return \Illuminate\Http\JsonResponse
    //  *
    //  * This method handles the creation of a new homework assignment. It retrieves
    //  * the necessary input data from the request, validates the course and user
    //  * permissions, and checks for existing homework if the type is 'single'.
    //  * Depending on the question type, it either requires a file upload or a link.
    //  * Finally, it delegates the storage operation to the question service.
    //  *
    //  * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the course is not found.
    //  * @throws \Symfony\Component\HttpKernel\Exception\HttpException If the user is not authorized to create homework for the course.
    //  * @throws \Symfony\Component\HttpKernel\Exception\HttpException If required file or link is missing.
    // */
    // public function store(StoreHomeworkRequest $request)
    // {
    //     $course_id     = $request->input('course_id');
    //     $chapter_id    = $request->input('chapter_id');
    //     $lesson_id     = $request->input('lesson_id');
    //     $title         = $request->input('title');
    //     $instruction   = $request->input('instruction');
    //     $link          = $request->input('link');
    //     $deadline      = $request->input('deadline');
    //     $type          = $request->input('type');
    //     $question_type = $request->input('question_type');

    //     $user   = User::find(Auth::id());
    //     $course = Course::find($course_id);

    //     if(!$course):
    //         return response()->json(['message' => 'Course not found'], 404);
    //     endif;

    //     if($course->created_by !== $user->id):
    //         return response()->json(['message' => 'You are not authorized to create homework for this course'], 403);
    //     endif;

    //     $file = null;

    //     if($question_type === 'files'){
    //         if(empty($request->file('file'))):
    //             return response()->json(['message' => 'File is required'], 400);
    //         endif;

    //         if($request->hasFile('file')):
    //             $file = $request->file('file');
    //         endif;
    //     }else{
    //         if(!$link):
    //             return response()->json(['message' => 'Link is required']);
    //         endif;
    //     }

    //     if($type === 'single'):
    //         if(Homework::where('course_id', $course->id)->exists()):
    //             return response()->json(['message' => 'You already created a homework for this course.']);
    //         endif;
    //     endif;

    //     return $this->questionServiceObj->store($course_id, $chapter_id, $lesson_id, $title, $instruction, $file, $link, $deadline, $type,$question_type);
    // }

    // public function update(Request $request)
    // {
    //     return 'This is waiting for development';
    // }

    // /**
    //  * Checks the homework based on the provided request data.
    //  *
    //  * @param CheckHomeworkRequest $request The request object containing homework details.
    //  * @return mixed The result of the homework check operation.
    // */
    // public function check(CheckHomeworkRequest $request)
    // {
    //     $homeworkId = $request->input('homework_id');
    //     $studentId  = $request->input('student_id');
    //     $score      = $request->input('score');
    //     $comment    = $request->input('comment');

    //     return $this->homeworkServiceObj->checkHomework($homeworkId, $studentId, $score, $comment);
    // }

    // /**
    //  * Handles the submission of a homework request.
    //  *
    //  * @param StoreWorkRequest $request The request object containing the homework submission data.
    //  * @return mixed The result of the homework service store method.
    // */
    // public function submit(StoreWorkRequest $request) :mixed
    // {
    //     $homeworkId = $request->input('homework_id');

    //     if($request->hasFile('file')){
    //         $file = $request->file('file');
    //     }

    //     return $this->homeworkServiceObj->store($homeworkId, $file);
    // }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'title' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'deadline' => 'nullable|date',
            'questions' => 'required|array',
            'questions.*.label' => 'nullable|string',
            'questions.*.question' => 'required|string',
        ]);

        $homework = Homework::create([
            'course_id' => $request->course_id,
            'chapter_id' => $request->chapter_id,
            'lesson_id' => $request->lesson_id,
            'title' => $request->title,
            'deadline' => $request->deadline,
        ]);

        foreach ($validated['questions'] as $q) {
            $homework->questions()->create([
                'label' => $q['label'],
                'question' => $q['question'],
            ]);
        }

        return response()->json([
            'message' => 'Homework created successfully',
            'data' => $homework->load('questions')
        ], 201);
    }



}
