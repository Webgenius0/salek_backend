<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Services\CourseService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Requests\LessonStoreRequest;
use App\Http\Requests\ChapterStoreRequest;
use App\Models\Chapter;
use App\Models\User;
use App\Services\HelperService;
use App\Traits\ApiResponse;

class CourseController extends Controller
{
    use ApiResponse;
    
    public $courseServiceObj;

    public function __construct()
    {
        $this->courseServiceObj = new CourseService();
    }

    /**
     * Index method
     * Get all courses
     * here this method is used to get all courses witrhout purchased courses
     * @return mixed
    */
    public function index()
    {
        $userId = Auth::id();
        
        $courses = Course::with(['chapters.lessons', 'category', 'creator'])
            ->whereDoesntHave('purchasers', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->get();

        $data = $courses->map(function($course){
            return [
                'course_id'    => $course->id,
                'course_title' => $course->name,
                'price'        => $course->price,
                'review'       => 4.9 . (232 . ' Reviews'),
                'total_chapter'  => $course->chapters->count(),
                'total_level' => $course->chapters->max('chapter_order'),
                'total_class'  => $course->total_class,
                'students'     => $course->purchasers->count(),
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'All Courses',
            'data'    => $data,
            'code'    => 200,
        ], 200);
    }

    /**
     * Course List
     * Get all courses
     *
     * @return mixed
    */
    public function courseList()
    {
        return $this->courseServiceObj->courseList();
    }

    /**
     * Popular Courses
     * Calculate popularity based on reviews,ratings,purchase history
     *
     * @return mixed
    */
    public function popularCourse()
    {
        return $this->courseServiceObj->popularCourse();
    }

    /**
     * course store method
     *
     * @param CourseStoreRequest $request
     * @return mixed
    */
    public function store(CourseStoreRequest $request)
    {
        $creatorId          = request()->user()->id;
        $name               = $request->input('name');
        $description        = $request->input('description');
        $category_id        = $request->input('category_id');
        $totalClass         = $request->input('total_class');
        $price              = $request->input('price');
        $total_month        = $request->input('total_month');
        $additional_charge  = $request->input('additional_charge');
        $introduction_title = $request->input('introduction_title');

        $cover_photo = null;
        if($request->hasFile('cover_photo')) {
            $cover_photo = $request->file('cover_photo');
        }

        $class_video = null;
        if($request->hasFile('class_video')) {
            $class_video = $request->file('class_video');
        }

        return $this->courseServiceObj->store(
            (int) $creatorId,
            (string) $name,
            (string) $description,
            (int) $category_id,
            (int) $totalClass,
            (int) $price,
            (int) $total_month,
            (int) $additional_charge,
            (string) $introduction_title,
            $cover_photo,
            $class_video
        );
    }

    /**
     * Store a new chapter for a course.
     *
     * @param ChapterStoreRequest $request The request object containing the chapter details.
     * @return \Illuminate\Http\JsonResponse The response object containing the status and message.
    */
    public function chapterStore(ChapterStoreRequest $request)
    {
        $course_id     = $request->input('course_id');
        $name          = $request->input('name');

        $user   = User::find(Auth::id());
        $course = Course::find($course_id);

        if($user->id != $course->created_by) {
            return $this->failedResponse('You have no permission to access this course', 403);
        }

        $prevoiusChapter = Chapter::where('course_id', $course_id)->get()->toArray();

        if(empty($prevoiusChapter)){
            return $this->courseServiceObj->chapterStore($course_id, $name, 'beginner', 1);
        }
        
        $totalChapter = count($prevoiusChapter);
        
        $difficultyLevel = HelperService::getDifficultyLevel($totalChapter + 1);

        return $this->courseServiceObj->chapterStore($course_id, $name, $difficultyLevel['level'], $difficultyLevel['order']);
    }

    /**
     * Lesson Store method
     *
     * @param LessonStoreRequest $request
     * @return mixed
    */
    public function lessonStore(LessonStoreRequest $request)
    {
        $course_id    = $request->input('course_id');
        $chapter_id   = $request->input('chapter_id');
        $name         = $request->input('name');
        $video        = $request->file('video_url');
        $duration     = $request->input('duration');

        $user   = User::find(Auth::id());
        $course = Course::find($course_id);

        $checkItem = HelperService::checkItemByCourse($course_id, $chapter_id);

        if(!$checkItem){
            return $this->failedResponse('This chapter not exists in your selected course', 404);
        }

        if($user->id != $course->created_by) {
            return $this->failedResponse('You have no permission to access this course', 403);
        }

        return $this->courseServiceObj->lessonStore($course_id, $chapter_id, $name, $video, $duration);
    }

    /**
     * Course Details method
     * call the service class method
     *
     * @param [string] $id
     * @return mixed
    */
    public function show($id)
    {
        return $this->courseServiceObj->show($id);
    }
    
    /**
     * Current Course
     * Get the current course of a user
     *
     * @return mixed
    */
    public function currentCourse()
    {
        $user = request()->user();

        return $this->courseServiceObj->currentCourse($user);
    }

    /**
     * Course wise chapter
     * Get all chapters of a course
     *
     * @param [string] $id
     * @return mixed
    */
    public function courseWiseChapter($id)
    {
        return $this->courseServiceObj->courseWiseChapter($id);
    }

    /**
     * Retrieves a course with its associated chapters and lessons by the given course ID.
     *
     * @param int $id The ID of the course to retrieve.
     * @return mixed The course with its associated chapters and lessons.
    */
    public function courseWithClass($id)
    {
        $course = Course::with('chapters.lessons')->where('id', $id)->first();

        return $this->courseServiceObj->courseWithClass($course);
    }

    /**
     * Course Achievement
     * Get all achievements of a course
     *
     * @param [string] $id
     * @return mixed
    */
    public function courseAchievement($id)
    {
        $course = Course::with('chapters.lessons')->where('id', $id)->first();

        return $this->courseServiceObj->courseAchievement($course);
    }

    /**
     * Ongoing Course
     * Get all ongoing courses
     *
     * @return mixed
    */
    public function ongoingCourse()
    {
        return $this->courseServiceObj->ongoingCourse();
    }

    /**
     * Complete Course
     * Get all completed courses
     *
     * @return mixed
    */
    public function completeCourse()
    {
        return $this->courseServiceObj->completeCourse();
    }

    /**
     * Retrieve the level of a course based on the provided ID.
     *
     * @param int $id The ID of the course.
     * @return mixed The level of the course.
    */
    public function level($id)
    {
        return $this->courseServiceObj->level($id);
    }

    /**
     * All Achievement
     * Get all achievements of a course
     *
     * @return mixed
    */
    public function allAchievement()
    {
        return response()->json(['message' => 'This is Course Achievement panel.This is now under working..']);
    }

    /**
     * Show Progress
     * Get the progress of a course
     *
     * @param [string] $id
     * @return mixed
    */
    public function showProgress($id)
    {
        return $this->courseServiceObj->showProgress($id);
    }
}
