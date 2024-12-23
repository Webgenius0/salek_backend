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
use App\Models\LessonUser;
use App\Services\HelperService;

class VideoController extends Controller
{
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

        $data = [
            'name'      => $video->name,
            'video_url' => $video->video_url,
            'duration'  => $video->duration,
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
        $watchedTime = $request->input('watched_time');
        
        if(!$watchedTime){
            return response()->json(['message' => 'Watched time is required.!']);
        }
        
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

        $totalDuration = $video->duration * 60;
        $watchedTime += $lessonUser->watched_time;
        
        if($watchedTime >= $totalDuration){
            $lessonUser->completed    = 1;
            $lessonUser->completed_at = now();
            $lessonUser->watched_time = $totalDuration;

            $lessonUser->save();

            return response()->json([
                'message' => 'Your video is completely seen'
            ]);
        }

        $lessonUser->watched_time = $watchedTime;
        $lessonUser->save();

        return response()->json([
            'message' => 'Your video watched time added.',
            'data'    => $video,
        ], 200);
    }
}
