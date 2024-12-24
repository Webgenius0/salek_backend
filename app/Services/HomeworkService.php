<?php

namespace App\Services;

use App\Models\User;
use App\Models\Homework;
use App\Models\CourseUser;
use App\Traits\ApiResponse;
use App\Models\StudentHomework;
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

            if($studentHomework): 
                return $this->failedResponse('You already submit the work.', 422);
            endif;

            $studentHomeworkObj = new StudentHomework();

            if($file != null){
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = 'uploads/homework/submit/' . $filename;
                $file->move(public_path('uploads/homework/submit'), $filename);
                $studentHomeworkObj->answer_script = $filePath;
            }

            $homeworkStatus = now()->greaterThan($homework->deadline) ? 'late' : 'in_time';

            $studentHomeworkObj->user_id       = $user->id;
            $studentHomeworkObj->homework_id   = $homeworkId;
            $studentHomeworkObj->submission_at = now();
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
}
