<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ShowVideoRequest;
use App\Models\Homework;
use App\Models\Lesson;
use App\Models\LessonUser;
use App\Models\StudentHomework;
use App\Models\StudentProgress;
use App\Services\HelperService;
use App\Services\VideoService;

class VideoController extends Controller
{
    public $videoServiceObj;

    public function __construct()
    {
        $this->videoServiceObj = new VideoService();
    }

    /**
     * Display the specified resource.
     *
     * @param  ShowVideoRequest  $request
     * @return mixed
    */
    public function show(ShowVideoRequest $request) : mixed
    {
        $user = User::find(Auth::id());

        if(!$user || $user->role !== 'student') {
            return response()->json(['message' => 'User not found or You are not permitted.'], 404);
        }

        $courseId  = $request->input('course_id');
        $chapterId = $request->input('chapter_id');
        $lessonId  = $request->input('lesson_id');

        $course = Course::with(['chapters.lessons'])->where('id',$courseId)->first();

        if(!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        $checkCourse = CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->where('access_granted', 1)->first();

        if(!$checkCourse) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 404);
        }

        $video = $course->chapters->where('id', $chapterId)->first()->lessons->where('id', $lessonId)->first();

        if(!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        $lessonUser = LessonUser::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lessonId],
            ['updated_at' => now()]
        );

        StudentProgress::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $courseId],
        );

        $studentProgress = Homework::where('course_id', $course->id)->first();
        if($studentProgress):
            StudentHomework::updateOrCreate(
                ['user_id' => $user->id, 'homework_id' => $studentProgress->id]
            );
        endif;

        $data = [
            'name'        => $video->name,
            'video_url'   => $video->video_url,
            'duration'    => $video->duration,
            'last_seen'   => $lessonUser->watched_time ?? 0,
            'score'       => $lessonUser->score ?? 0,
            'is_complete' => (bool) $lessonUser->completed,
        ];

        return response()->json(['message' => 'Video found.', 'data' => $data], 200);
    }

    /**
     * Update the specified resource in public.
     *
     * @param  ShowVideoRequest  $request
     * @return mixed
    */
    public function update(ShowVideoRequest $request) : mixed
    {
        $user = User::find(Auth::id());

        if(!$user || $user->role !== 'student') {
            return response()->json(['message' => 'User not found or You are not permitted.'], 404);
        }

        $watchedTime = $request->input('watched_time');
        $courseId    = $request->input('course_id');
        $chapterId   = $request->input('chapter_id');
        $lessonId    = $request->input('lesson_id');


        $course = Course::with(['chapters.lessons', 'homework'])->where('id',$courseId)->first();

        $totalHomework = $course->homework->count();

        if(!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        if (!CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->where('access_granted', 1)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $video = $course->chapters
            ->where('id', $chapterId)
            ->flatMap->lessons
            ->where('id', $lessonId)
            ->first();

        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        $lessonUser = LessonUser::firstOrNew(
            ['user_id' => $user->id, 'lesson_id' => $lessonId]
        );

        if($lessonUser->completed == 1){
            return response()->json([
                'status'  => false,
                'message' => 'Your progress already updated.!',
                'code'    => 422
            ]);
        }

        $totalCourseNumber     = ($totalHomework > 0) ? 80 : 100;
        $totalDuration         = $video->duration * 60;
        $perlessonNumber       = $totalCourseNumber / $course->lessons->count();
        $persecondLessonNumber = $perlessonNumber / $totalDuration;


        $watchedTime += $lessonUser->watched_time;
        if($watchedTime >= $totalDuration){

            if($watchedTime == $totalDuration){
                $earnpoint = $watchedTime * $persecondLessonNumber;
                $earnpoint = round($earnpoint, 2);

                $lessonUser->score        += $earnpoint;
                $lessonUser->completed     = 1;
                $lessonUser->completed_at  = now();
                $lessonUser->watched_time  = $totalDuration;
                $lessonUser->updated_at    = now();

                $lessonUser->save();

                $this->videoServiceObj->progressCalucate($user->id, $course->id, $earnpoint);

                return response()->json([
                    'message' => 'Your video is completely seen'
                ]);
            }

            $watchedTime = $watchedTime - $totalDuration;
            $earnpoint   = $watchedTime * $persecondLessonNumber;
            $earnpoint   = round($earnpoint, 2);

            $lessonUser->score        += $earnpoint;
            $lessonUser->completed     = 1;
            $lessonUser->completed_at  = now();
            $lessonUser->watched_time  = $totalDuration;
            $lessonUser->updated_at    = now();

            $lessonUser->save();

            $this->videoServiceObj->progressCalucate($user->id, $course->id, $earnpoint);

            return response()->json([
                'message' => 'Your video is completely seen'
            ]);
        }

        $earnpoint = $watchedTime * $persecondLessonNumber;

        $lessonUser->watched_time  = $watchedTime;
        $lessonUser->score        += $earnpoint;
        $lessonUser->updated_at    = now();

        $res = $lessonUser->save();

        if($res){
            $this->videoServiceObj->progressCalucate($user->id, $course->id, $earnpoint);
            return response()->json([
                'message' => 'Your video watched time added.',
                'data'    => $video,
            ], 200);
        }

        return response()->json([
            'message' => 'Your video watched time added.',
            'data'    => $video,
        ], 200);
    }
}
