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
use Illuminate\Support\Facades\Auth;

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

    public function getWeeklyCompletionRate($courseId)
    {
        $user = Auth::user();

        // Fetch the course with lessons
        $course = Course::with(['lessons'])->find($courseId);

        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        // Initialize an array for weekly completion rates
        $weeklyCompletionRates = [];

        // Calculate completion rates per week (assuming lessons are divided by week)
        foreach ($course->lessons as $lesson) {
            $lessonUser = LessonUser::where('user_id', $user->id)
                                    ->where('lesson_id', $lesson->id)
                                    ->first();

            if ($lessonUser) {
                // Get the week number for each completed lesson (using the completed_at field or lesson_order)
                $weekNumber = Carbon::parse($lessonUser->completed_at)->weekOfYear;

                if (!isset($weeklyCompletionRates[$weekNumber])) {
                    $weeklyCompletionRates[$weekNumber] = 0;
                }

                // If the lesson is completed, increment the completion count for that week
                if ($lessonUser->completed) {
                    $weeklyCompletionRates[$weekNumber]++;
                }
            }
        }

        // Total number of lessons in the course
        $totalLessons = $course->lessons->count();

        // Total number of lessons per week (if evenly distributed, you can adjust the number of weeks)
        $totalWeeks = 7;
        $lessonsPerWeek = ceil($totalLessons / $totalWeeks);

        // Calculate the completion rate for each of the 7 weeks
        $weeklyRates = [];

        for ($week = 1; $week <= $totalWeeks; $week++) {
            // If no lessons were completed for a specific week, set it to 0%
            $completedLessonsInWeek = isset($weeklyCompletionRates[$week]) ? $weeklyCompletionRates[$week] : 0;

            // Calculate completion rate for the week
            $completionRate = round(($completedLessonsInWeek / $lessonsPerWeek) * 100);

            // Store the completion rate for the week
            $weeklyRates[$week] = $completionRate;
        }

        return response()->json([
            'weekly_completion_rates' => $weeklyRates
        ], 200);
    }
}
