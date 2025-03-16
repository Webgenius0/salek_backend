<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\LessonUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AchievementController extends Controller
{

    public function getUserAchievements(Request $request)
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'student') {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $achievements = $this->checkAchievements($user->id);

        // Calculate completion percentage
        $totalPossibleAchievements = 5; // Total types of achievements
        $achievedCount = count($achievements);
        $completionPercentage = round(($achievedCount / $totalPossibleAchievements) * 100);

        return response()->json([
            'message' => 'Achievements fetched successfully.',
            'data' => $achievements,
            'completion_percentage' => $completionPercentage
        ], 200);
    }

    public function checkAchievements($userId)
    {
        $user = User::find($userId);
        if (!$user || $user->role !== 'student') {
            return null;
        }

        $achievements = [];

        // Greetings: Complete any lesson 10 times
        $lessonCompletionCounts = LessonUser::where('user_id', $userId)
            ->where('completed', 1)
            ->select('lesson_id', DB::raw('count(*) as completion_count'))
            ->groupBy('lesson_id')
            ->get();

        $hasGreetingsAchievement = $lessonCompletionCounts->contains(function ($item) {
            return $item->completion_count >= 10;
        });

        if ($hasGreetingsAchievement) {
            $achievements[] = [
                'type' => 'Greetings',
                'description' => 'You have completed this lesson 10 times.',
                'icon' => 'trophy',
                'rating' => 5
            ];
        }

        // Dedicated Learner: Complete 50 lessons in total
        $totalCompletedLessons = LessonUser::where('user_id', $userId)
            ->where('completed', 1)
            ->count();

        if ($totalCompletedLessons >= 50) {
            $achievements[] = [
                'type' => 'Dedicated Learner',
                'description' => 'You have completed 50 lessons in total.',
                'icon' => 'book',
                'rating' => 5
            ];
        }

        // Marathon Watcher: Complete a single lesson 20 times
        $marathonAchievement = $lessonCompletionCounts->contains(function ($item) {
            return $item->completion_count >= 20;
        });

        if ($marathonAchievement) {
            $achievements[] = [
                'type' => 'Marathon Watcher',
                'description' => 'You have completed a single lesson 20 times.',
                'icon' => 'play-circle',
                'rating' => 4
            ];
        }

        // Course Explorer: Complete all lessons in a course
        $enrolledCourses = CourseUser::where('user_id', $userId)
            ->where('access_granted', 1)
            ->pluck('course_id');

        foreach ($enrolledCourses as $courseId) {
            $course = Course::with('chapters.lessons')->find($courseId);
            $allLessonIds = [];

            foreach ($course->chapters as $chapter) {
                foreach ($chapter->lessons as $lesson) {
                    $allLessonIds[] = $lesson->id;
                }
            }

            $completedLessonsCount = LessonUser::where('user_id', $userId)
                ->whereIn('lesson_id', $allLessonIds)
                ->where('completed', 1)
                ->distinct('lesson_id')
                ->count();

            if ($completedLessonsCount == count($allLessonIds) && count($allLessonIds) > 0) {
                $achievements[] = [
                    'type' => 'Course Explorer',
                    'description' => "You have completed all lessons in {$course->title}.",
                    'icon' => 'compass',
                    'rating' => 4
                ];
            }
        }

        // Quick Starter: Complete 5 lessons within the first week of enrollment
        foreach ($enrolledCourses as $courseId) {
            $enrollment = CourseUser::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            $oneWeekAfterEnrollment = Carbon::parse($enrollment->created_at)->addWeek();

            $course = Course::with('chapters.lessons')->find($courseId);
            $allLessonIds = [];

            foreach ($course->chapters as $chapter) {
                foreach ($chapter->lessons as $lesson) {
                    $allLessonIds[] = $lesson->id;
                }
            }

            $completedLessonsInFirstWeek = LessonUser::where('user_id', $userId)
                ->whereIn('lesson_id', $allLessonIds)
                ->where('completed', 1)
                ->where('completed_at', '<=', $oneWeekAfterEnrollment)
                ->distinct('lesson_id')
                ->count();

            if ($completedLessonsInFirstWeek >= 5) {
                $achievements[] = [
                    'type' => 'Quick Starter',
                    'description' => 'You completed 5 lessons within your first week.',
                    'icon' => 'zap',
                    'rating' => 4
                ];
            }
        }

        return $achievements;
    }
}
