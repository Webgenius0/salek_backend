<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Homework;
use App\Models\LessonUser;
use App\Models\StudentHomework;
use App\Models\StudentProgress;

class VideoService extends Service
{
    public function progressCalucate($userId, $courseId, $earnpoint)
    {
        $homeworkNumber = 0;
        
        $studentProgress = StudentProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
        );
        
        $homework = Homework::where('course_id', $courseId)->first();
        if($homework){
            $studentProgress->lesson_progress   += $earnpoint;
            $homeworkInfo = StudentHomework::where('homework_id', $homework->id)->where('user_id', $userId)->first();
            if($homeworkInfo):
                $homeworkNumber = $homeworkInfo->score;
            endif;
            $studentProgress->homework_progress += $homeworkNumber;

            $courseLesson = Course::with('lessons')->where('id', $courseId)->first();

            $courseLessonIds = $courseLesson->lessons->pluck('id');
            $lessons = LessonUser::whereIn('lesson_id', $courseLessonIds)->where('user_id', $userId)->get();
            if($lessons->isEmpty()){
                $courseScore = 0;
            }
            
            $courseScore = $lessons->sum('score') + $homeworkNumber;


            $studentProgress->course_progress = $courseScore;
            $studentProgress->save();

            return true;
        }

        $studentProgress->lesson_progress   += $earnpoint;
        $studentProgress->course_progress   += $earnpoint;
        $studentProgress->homework_progress += 0;
        $studentProgress->save();

        return true;
    }
}
