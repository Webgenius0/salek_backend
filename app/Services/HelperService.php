<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseUser;
use Illuminate\Support\Facades\Auth;

class HelperService extends Service
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Uploads a file to the specified path.
     *
     * @param \Illuminate\Http\UploadedFile $file The file to be uploaded.
     * @param string $path The destination path where the file should be uploaded.
     * @return string The path to the uploaded file.
    */
    public static function fileUpload($file, $path)
    {
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $fileName);
        return $path . '/' . $fileName;
    }

    /**
     * Updates the course user record for the given course ID.
     * 
     * This method checks if a CourseUser record exists for the given course ID and the currently authenticated user.
     * If no such record exists, it creates a new CourseUser record with access_granted set to 0.
     * If the record exists, it updates the access_granted field to 0.
     *
     * @param int $itemId The ID of the course.
     * @return void
    */
    public static function updateCourseUser($itemId): void
    {
        $checkCourseUser = CourseUser::where('course_id', $itemId)->where('user_id', Auth::id())->first();

        if (!$checkCourseUser):
            $courseUserObj = new CourseUser();

            $courseUserObj->user_id = Auth::id();
            $courseUserObj->course_id = $itemId;
            $courseUserObj->access_granted = 0;

            $courseUserObj->save();
        else:
            $checkCourseUser->access_granted = 0;
            $checkCourseUser->save();
        endif;
    }

    /**
     * Check if a chapter exists in a course.
     *
     * This method checks whether a chapter with the specified ID exists within a course
     * identified by the given course ID. It retrieves the course along with its chapters
     * and verifies if the chapter is part of the course.
     *
     * @param int $courseId The ID of the course to check.
     * @param int $chapterId The ID of the chapter to check for existence within the course.
     * @return bool Returns true if the chapter exists in the course, false otherwise.
    */
    public static function checkItemByCourse($courseId, $chapterId) :bool
    {
        $course = Course::with('chapters')->findOrFail($courseId);

        $isChapterExists = $course->chapters->contains('id', $chapterId);

        return $isChapterExists;
    }


    /**
     * Determine the difficulty level based on the chapter order.
     *
     * @param int $chapterNumber
     * @return string
    */
    public static function getDifficultyLevel($chapterNumber)
    {
        if ($chapterNumber <= 2) {
            $data = [
                'level' => 'beginner',
                'order' => 1,
            ];
            return $data;
        } elseif ($chapterNumber <= 4) {
            $data = [
                'level' => 'intermediate',
                'order' => 2,
            ];
            return $data;
        } else {
            $data = [
                'level' => 'advanced',
                'order' => 3,
            ];
            return $data;
        }
    }
    
    /**
     * Check if the user is authenticated.
     *
     * This method checks if the user is authenticated. If the user is not authenticated,
     * it logs out the user using the Auth::logout() method.
     *
     * @return mixed
    */
    public function checkUser()
    {
        if (!$this->user) {
            Auth::logout();
        }
    }
}
