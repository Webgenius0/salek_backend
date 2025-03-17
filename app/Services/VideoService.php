<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Homework;
use App\Models\LessonUser;
use App\Models\StudentHomework;
use App\Models\StudentProgress;

class VideoService extends Service
{
    public function progressCalucate($userId, $courseId, $completionRate)
    {
        // Fetch the student's progress record, or create a new one if it doesn't exist
        $studentProgress = StudentProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
        );

        // Fetch the course to get the total number of lessons
        $course = Course::with('lessons')->where('id', $courseId)->first();
        if (!$course) {
            return false; // If the course doesn't exist, return false
        }

        // Total number of lessons in the course
        $totalLessons = $course->lessons->count();

        // Get the number of lessons completed by the user
        $completedLessons = LessonUser::where('user_id', $userId)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->where('completed', 1) // Ensure only completed lessons are counted
            ->count();

        // Calculate the new course progress based on completed lessons
        $newCompletionRate = round(($completedLessons / $totalLessons) * 100);

        // Update course progress
        $studentProgress->course_progress = $newCompletionRate;

        // Save the updated progress
        $studentProgress->save();

        return true;
    }
}
