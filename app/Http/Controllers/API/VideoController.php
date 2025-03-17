<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Homework;
use App\Models\CourseUser;
use App\Models\LessonUser;
use Illuminate\Http\Request;
use App\Services\VideoService;
use App\Models\StudentHomework;
use App\Models\StudentProgress;
use App\Services\HelperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ShowVideoRequest;

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
    public function show(ShowVideoRequest $request): mixed
    {
        $user = User::find(Auth::id());

        if (!$user || $user->role !== 'student') {
            return response()->json(['message' => 'User not found or You are not permitted.'], 404);
        }

        $courseId  = $request->input('course_id');
        $chapterId = $request->input('chapter_id');
        $lessonId  = $request->input('lesson_id');

        $course = Course::with(['chapters.lessons'])->where('id', $courseId)->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        $checkCourse = CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->where('access_granted', 1)->first();

        if (!$checkCourse) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 404);
        }

        $video = $course->chapters->where('id', $chapterId)->first()->lessons->where('id', $lessonId)->first();

        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        $chapter = $course->chapters->where('id', $chapterId)->first();

        // Load the level from the chapter
        $chapter->load('level');
        $level = $chapter->level;

        $lessonUser = LessonUser::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lessonId],
            ['updated_at' => now()]
        );

        StudentProgress::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $courseId],
        );


        $data = [
            'name'        => $video->name,
            'video_url'   => $video->video_url,
            'duration'    => $video->duration,
            'last_seen'   => $lessonUser->watched_time ?? 0,
            'score'       => $lessonUser->score ?? 0,
            'is_complete' => (bool) $lessonUser->completed,
            'level_id'    => $level ? $level->id : null,
        ];

        return response()->json(['message' => 'Video found.', 'data' => $data], 200);
    }

    /**
     * Update the specified resource in public.
     *
     * @param  ShowVideoRequest  $request
     * @return mixed
     */

    public function update(ShowVideoRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'student') {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $watchedTime = $request->input('watched_time');
        $courseId    = $request->input('course_id');
        $chapterId   = $request->input('chapter_id');
        $lessonId    = $request->input('lesson_id');

        // Fetch the course with lessons
        $course = Course::with(['chapters.lessons', 'homework'])->find($courseId);

        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        if (!CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->where('access_granted', 1)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        // Find the requested lesson
        $video = Lesson::where('chapter_id', $chapterId)->where('id', $lessonId)->first();

        if (!$video) {
            return response()->json(['message' => 'Lesson not found.'], 404);
        }

        // Fetch or create a progress record
        $lessonUser = LessonUser::firstOrNew(['user_id' => $user->id, 'lesson_id' => $lessonId]);

        if ($lessonUser->completed) {
            return response()->json([
                'status'       => true,
                'message'      => 'Lesson already completed.',
                'is_complete'  => true,
                'score'        => $lessonUser->score,
                'watched_time' => $lessonUser->watched_time,
                'next_lesson_id' => Lesson::where('chapter_id', $chapterId)
                    ->where('id', '>', $lessonId)
                    ->orderBy('id')
                    ->first()
                    ?->id
            ]);
        }

        // Calculate lesson duration in seconds
        $totalDuration = $video->duration * 60;

        // Ensure watched time doesn't exceed total duration
        $watchedTime = min($lessonUser->watched_time + $watchedTime, $totalDuration);

        // Calculate points
        $totalHomework = $course->homework ? $course->homework->count() : 0;
        $totalCourseNumber = ($totalHomework > 0) ? 80 : 100;
        $perLessonPoints = $totalCourseNumber / $course->lessons->count();
        $perSecondPoints = $perLessonPoints / $totalDuration;
        $earnpoint = round($watchedTime * $perSecondPoints, 2);

        // Update lesson progress
        $lessonUser->watched_time = $watchedTime;
        $lessonUser->score += $earnpoint;

        if ($watchedTime >= $totalDuration) {
            $lessonUser->completed = 1;
            $lessonUser->completed_at = now();
        }

        $lessonUser->save();


        // Update course progress based on number of completed lessons
        $totalLessons = $course->lessons->count();
        dd($totalLessons);
        $completedLessons = $course->lessons()->whereHas('lessonUsers', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('completed', 1);
        })->count();

        $completionRate = round(($completedLessons / $totalLessons) * 100);

        // Always get the next lesson, regardless of completion status
        $nextLesson = Lesson::where('chapter_id', $chapterId)
            ->where('id', '>', $lessonId)
            ->orderBy('id')
            ->first();

        // Update student progress based on calculated completion rate
        // $this->progressCalucate($user->id, $course->id, $completionRate);

        Log::info("Updating progress for user: $user->id, course: $course->id, completion rate: $completionRate");

        // Fetch or create the progress record
        $studentProgress = StudentProgress::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['course_progress' => $completionRate]
        );

        // Save the progress to the database
        $studentProgress->save();

        Log::info("Progress updated successfully for user: $user->id, course: $course->id");


        return response()->json([
            'message'         => $lessonUser->completed ? 'Lesson completed' : 'Lesson progress updated',
            'next_lesson_id'  => $nextLesson ? $nextLesson->id : null,
            'is_complete'     => $lessonUser->completed,
            'score'           => $lessonUser->score,
            'watched_time'    => $lessonUser->watched_time
        ], 200);
    }

}
