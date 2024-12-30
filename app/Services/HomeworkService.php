<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\Homework;
use App\Models\CourseUser;
use App\Traits\ApiResponse;
use App\Models\StudentHomework;
use App\Models\StudentProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeworkService extends Service
{
    use ApiResponse;
    
    /**
     * Store the submitted homework file for a specific homework ID.
     *
     * @param int $homeworkId The ID of the homework to be submitted.
     * @param \Illuminate\Http\UploadedFile|null $file The file to be uploaded as the homework submission.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success or failure of the operation.
     *
     * @throws \Exception If there is an error during the database transaction.
    */
    public function store($homeworkId, $file)
    {
        try {
            DB::beginTransaction();

            $homework = Homework::where('id', $homeworkId)->first();

            if(!$homework): 
                return $this->failedResponse('Homework not found', 404);
            endif;

            $user = User::find(Auth::id());

            $courseUser = CourseUser::where('user_id', $user->id)->where('course_id', $homework->course_id)->first();

            if(!$courseUser): 
                return $this->failedResponse('This course is not permitted for you', 404);
            endif;

            $studentHomework = StudentHomework::where('user_id', $user->id)->where('homework_id', $homeworkId)->first();

            $homeworkStatus = now()->greaterThan($homework->deadline) ? 'late' : 'in_time';

            $filePath = null;
            
            if($file != null){
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = 'uploads/homework/submit/' . $filename;
                $file->move(public_path('uploads/homework/submit'), $filename);

                if($studentHomework && $studentHomework->answer_script != null){
                    $previousFilePath = public_path($studentHomework->answer_script);
                    
                    if (file_exists($previousFilePath)) {
                        unlink($previousFilePath);
                    }
                }
            }

            if($studentHomework): 
                $studentHomework->answer_script = $filePath;
                $studentHomework->submission_at = now();
                $studentHomework->status = $homeworkStatus;

                $res = $studentHomework->save();
                DB::commit();
                if($res){
                    return $this->successResponse(true, 'Homework submit successfully', $studentHomework, 201);
                }
            endif;

            $studentHomeworkObj = new StudentHomework();

            $studentHomeworkObj->user_id       = $user->id;
            $studentHomeworkObj->homework_id   = $homeworkId;
            $studentHomeworkObj->submission_at = now();
            $studentHomeworkObj->answer_script = $filePath;
            $studentHomeworkObj->status        = $homeworkStatus;
            
            $res = $studentHomeworkObj->save();

            DB::commit();
            if($res){
                return $this->successResponse(true, 'Homework submit successfully', $studentHomeworkObj, 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedDBResponse('Database error', $e->getMessage(), 422);
        }
    }

    /**
     * Check and update the homework score and comment for a student.
     *
     * This function performs several checks to ensure the validity of the homework, course, and student.
     * It updates the score and comment for the student's homework and also updates the student's progress in the course.
     *
     * @param int $homeworkId The ID of the homework to be checked.
     * @param int $studentId The ID of the student whose homework is being checked.
     * @param int $score The score to be assigned to the student's homework.
     * @param string $comment The comment to be added to the student's homework.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the operation.
     */
    public function checkHomework($homeworkId, $studentId, $score, $comment)
    {
        $homework = Homework::find($homeworkId);
        if(!$homework):
            return $this->failedResponse('Homework not found', 404);
        endif;

        $course = Course::find($homework->course_id);
        if(!$course):
            return $this->failedResponse('Course not found', 404);
        endif;

        if(!CourseUser::where('course_id', $course->id)->where('user_id', $studentId)->exists()):
            return $this->failedResponse('This student is authorized for this course', 403);
        endif;

        if($course->created_by !== Auth::id()):
            return $this->failedResponse('You are not authorized for this course', 403);
        endif;

        $studentHomework = StudentHomework::where('user_id', $studentId)->where('homework_id', $homeworkId)->first();
        if(!$studentHomework):
            return $this->failedResponse('Homework not found', 404);
        endif;

        $studentHomework->score      = $score;
        $studentHomework->comment    = $comment;
        $studentHomework->updated_at = now();

        $res = $studentHomework->save();

        if($res){
            $studentProgress = StudentProgress::where('user_id', $studentId)->where('course_id', $course->id)->first();
            if ($studentProgress) {
                $studentProgress->homework_progress = ($studentHomework->score !== null) ? $score : $studentProgress->homework_progress + $score;
                $studentProgress->save();
            }

            return $this->successResponse(true, 'Mark added to the homework.', $studentHomework, 200);
        }
    }
}
