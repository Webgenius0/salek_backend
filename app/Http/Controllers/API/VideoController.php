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

class VideoController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  ShowVideoRequest  $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function show(ShowVideoRequest $request) : JsonResponse
    {
        $user = User::find(Auth::id());
        
        if(!$user || $user->role !== 'student') {
            return response()->json(['message' => 'User not found or You are not permitted.'], 404);
        }

        $courseId  = $request->input('course_id');
        $chapterId = $request->input('chapter_id');
        $lessonId  = $request->input('lesson_id');

        if (LessonUser::where('user_id', $user->id)->where('completed', 0)->latest()->exists()) {
            return response()->json(['message' => 'Your previous video not seen properly.'], 403);
        }

        $course = Course::with(['chapters.lessons'])->where('id',$courseId)->first();

        if(!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        $checkCourse = CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->first();

        if(!$checkCourse) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 404);
        }

        $video = $course->chapters->where('id', $chapterId)->first()->lessons->where('id', $lessonId)->first();

        if(!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        return response()->json(['message' => 'Video found.', 'data' => $video], 200);
    }

    /**
     * Update the specified resource in public.
     *
     * @param  ShowVideoRequest  $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(ShowVideoRequest $request) : JsonResponse
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

        if (LessonUser::where('user_id', $user->id)->where('completed', 0)->latest()->exists()) {
            return response()->json(['message' => 'Your previous video not seen properly.'], 403);
        }

        if (!CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->exists()) {
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

        if ($lessonUser->exists && $lessonUser->completed) {
            return response()->json(['message' => 'You have already completed this video.'], 200);
        }

        $lessonUser->completed    = 1;
        $lessonUser->completed_at = now();
        $lessonUser->save();

        return response()->json([
            'message' => 'Your video is marked as complete.',
            'data'    => $video,
        ], 200);
    }
}
