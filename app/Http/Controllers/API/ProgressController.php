<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CourseUser;
use App\Models\LessonUser;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    use ApiResponse;

    protected $totalNumber;

    public function __construct()
    {
        $this->totalNumber = TOTAL_NUMBER;
    }
    
    public function store(Request $request)
    {
        $user     = User::find(Auth::id());
        $courseId = $request->input('course_id');

        $course = Course::with(['chapters.lessons'])->where('id', $courseId)->first();

        if(!$course){
            return $this->failedResponse('Course not found', 404);
        }
        
        $lessons = $course->chapters->flatMap(function ($chapter) {
            return $chapter->lessons;
        });

        $totalDuration = $lessons->sum('duration') * 60;
        $totalChapter  = $course->chapters->count();
        $totalLesson   = $lessons->count();
        $lessonIds     = $lessons->pluck('id');


        $courseUser = CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->exists();

        if(!$courseUser){
            return $this->failedResponse('This course is not authorize for you.', 403);
        }

        $userCourseDetails = LessonUser::whereIn('lesson_id', $lessonIds)->where('user_id', $user->id)->get();

        $completedLessons = $userCourseDetails
                        ->filter(function($value){
                            return $value->completed == 1;
                        })
                        ->map(function($value){
                            return $value->completed;
                        });

        $lessonWeight = $this->totalNumber / $totalLesson;
        
        $progress = 1 * $lessonWeight;

        $progress = floor($progress);

        $data = [
            'progress' => "Progress: {$progress}%",
            'lesson_weight' => $lessonWeight,
            'total_watched' => $userCourseDetails->sum('watched_time'),
        ];
        return $data;
    }
}
