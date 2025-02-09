<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Chapter;
use App\Models\CourseUser;
use App\Models\LessonUser;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Services\CourseService;
use App\Services\HelperService;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CourseStoreRequest;

use App\Http\Requests\LessonStoreRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ChapterStoreRequest;
use function PHPUnit\Framework\returnSelf;
use App\Http\Requests\LessonStoreRequestTwo;

class CourseController extends Controller
{
    use ApiResponse;

    public $courseServiceObj;

    public function __construct()
    {
        $this->courseServiceObj = new CourseService();
    }

    /**
     * Index method
     * Get all courses
     * here this method is used to get all courses witrhout purchased courses
     * @return mixed
     */
    public function index()
    {
        $userId = Auth::id();

        $courses = Course::with(['chapters.lessons', 'category', 'creator'])
            ->whereDoesntHave('purchasers', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('status', 'publish')
            ->latest()
            ->get();

        $data = $courses->map(function ($course) {
            return [
                'course_id'     => $course->id,
                'course_title'  => $course->name,
                'thumbnail'    => $course->cover_photo,
                'price'         => $course->price,
                'review'        => 4.9 . (232 . ' Reviews'),
                'total_chapter' => $course->chapters->count(),
                'total_level'   => $course->chapters->max('chapter_order'),
                'total_class'   => $course->total_class,
                'students'      => $course->purchasers->count(),
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'All Courses',
            'data'    => $data,
            'code'    => 200,
        ], 200);
    }

    /**
     * Course List
     * Get all courses
     *
     * @return mixed
     */
    public function courseList()
    {
        return $this->courseServiceObj->courseList();
    }

    /**
     * Popular Courses
     * Calculate popularity based on reviews,ratings,purchase history
     *
     * @return mixed
     */
    public function popularCourse()
    {
        return $this->courseServiceObj->popularCourse();
    }
    public function parentPopularCourse()
    {
        return $this->courseServiceObj->parentPopularCourse();
    }
    public function studentPopularCourse()
    {
        return $this->courseServiceObj->studentPopularCourse();
    }

    /**
     * course store method
     *
     * @param CourseStoreRequest $request
     * @return mixed
     */
    public function store(CourseStoreRequest $request)
    {
        $creatorId          = request()->user()->id;
        $name               = $request->input('name');
        $description        = $request->input('description');
        $category_id        = $request->input('category_id');
        $totalClass         = $request->input('total_class');
        $price              = $request->input('price');
        $total_month        = (int) $request->input('total_month');
        $additional_charge  = $request->input('additional_charge');
        $introduction_title = $request->input('introduction_title');
        $start_date         = $request->input('start_date');

        $cover_photo = null;
        if ($request->hasFile('cover_photo')) {
            $cover_photo = $request->file('cover_photo');
        }

        $class_video = null;
        if ($request->hasFile('class_video')) {
            $class_video = $request->file('class_video');
        }

        return $this->courseServiceObj->store(
            (int) $creatorId,
            (string) $name,
            (string) $description,
            (int) $category_id,
            (int) $totalClass,
            (int) $price,
            $start_date,
            (int) $total_month,
            (int) $additional_charge,
            (string) $introduction_title,
            $cover_photo,
            $class_video
        );
    }

    /**
     * Store a new chapter for a course.
     *
     * @param ChapterStoreRequest $request The request object containing the chapter details.
     * @return \Illuminate\Http\JsonResponse The response object containing the status and message.
     */
    public function chapterStore(ChapterStoreRequest $request)
    {
        $course_id     = $request->input('course_id');
        $name          = $request->input('name');

        $user   = User::find(Auth::id());
        $course = Course::find($course_id);

        if ($user->id != $course->created_by) {
            return $this->failedResponse('You have no permission to access this course', 403);
        }

        $prevoiusChapter = Chapter::where('course_id', $course_id)->get()->toArray();

        if (empty($prevoiusChapter)) {
            return $this->courseServiceObj->chapterStore($course_id, $name, 'beginner', 1);
        }

        $totalChapter = count($prevoiusChapter);

        $difficultyLevel = HelperService::getDifficultyLevel($totalChapter + 1);

        return $this->courseServiceObj->chapterStore($course_id, $name, $difficultyLevel['level'], $difficultyLevel['order']);
    }

    /**
     * Lesson Store method
     *
     * @param LessonStoreRequest $request
     * @return mixed
     */
    public function lessonStore(LessonStoreRequest $request)
    {
        $course_id    = $request->input('course_id');
        $chapter_id   = $request->input('chapter_id');
        $lesson_id    = $request->input('lesson_id');
        $video        = $request->file('video_url');
        $duration     = $request->input('duration');
        $photo      = $request->file('photo');

        $user   = User::find(Auth::id());
        $course = Course::find($course_id);

        // Handle voice memo update (delete old and upload new)
        if ($request->hasFile('photo')) {

            $photoPath = ImageService::uploadeImage($photo, 'lessons/');
        }

        $checkItem = HelperService::checkItemByCourse($course_id, $chapter_id);

        if (!$checkItem) {
            return $this->failedResponse('This chapter not exists in your selected course', 404);
        }

        if ($user->id != $course->created_by) {
            return $this->failedResponse('You have no permission to access this course', 403);
        }

        return $this->courseServiceObj->lessonStore($course_id, $chapter_id, $video, $duration, $lesson_id, $photoPath);
    }
    public function lessonStoreTwo(LessonStoreRequestTwo $request)
    {
        $course_id    = $request->input('course_id');
        $chapter_id   = $request->input('chapter_id');
        $name         = $request->input('name');

        $user   = User::find(Auth::id());
        $course = Course::find($course_id);

        $checkItem = HelperService::checkItemByCourse($course_id, $chapter_id);

        if (!$checkItem) {
            return $this->failedResponse('This chapter not exists in your selected course', 404);
        }

        if ($user->id != $course->created_by) {
            return $this->failedResponse('You have no permission to access this course', 403);
        }

        return $this->courseServiceObj->lessonStoreTwo($course_id, $chapter_id, $name);
    }

    /**
     * Course Details method
     * call the service class method
     *
     * @param [string] $id
     * @return mixed
     */
    public function show($id)
    {
        return $this->courseServiceObj->show($id);
    }
    public function showShort($id)
    {
        return $this->courseServiceObj->showShort($id);
    }

    /**
     * Current Course
     * Get the current course of a user
     *
     * @return mixed
     */
    public function currentCourse()
    {
        $user = request()->user();

        return $this->courseServiceObj->currentCourse($user);
    }

    /**
     * Course wise chapter
     * Get all chapters of a course
     *
     * @param [string] $id
     * @return mixed
     */
    public function courseWiseChapter($id)
    {
        return $this->courseServiceObj->courseWiseChapter($id);
    }

    /**
     * Retrieves a course with its associated chapters and lessons by the given course ID.
     *
     * @param int $id The ID of the course to retrieve.
     * @return mixed The course with its associated chapters and lessons.
     */
    public function courseWithClass($id)
    {
        $course = Course::with('chapters.lessons')->where('id', $id)->first();

        return $this->courseServiceObj->courseWithClass($course);
    }

    /**
     * Course Achievement
     * Get all achievements of a course
     *
     * @param [string] $id
     * @return mixed
     */
    public function courseAchievement($id)
    {
        $course = Course::with('chapters.lessons')->where('id', $id)->first();

        return $this->courseServiceObj->courseAchievement($course);
    }

    /**
     * Ongoing Course
     * Get all ongoing courses
     *
     * @return mixed
     */
    public function ongoingCourse()
    {
        return $this->courseServiceObj->ongoingCourse();
    }

    /**
     * Complete Course
     * Get all completed courses
     *
     * @return mixed
     */
    public function completeCourse()
    {
        return $this->courseServiceObj->completeCourse();
    }

    /**
     * Retrieve the level of a course based on the provided ID.
     *
     * @param int $id The ID of the course.
     * @return mixed The level of the course.
     */
    public function level($id)
    {
        return $this->courseServiceObj->level($id);
    }

    /**
     * All Achievement
     * Get all achievements of a course
     *
     * @return mixed
     */
    public function allAchievement($id)
    {
        $user = User::where('role', 'student')->find($id);

        if (!$user):
            return $this->failedResponse('User not found', 404);
        endif;

        return $this->successResponse(true, 'This id is the student id.This is Course Achievement panel.This is now under working..', $user, 200);
    }

    /**
     * Publish a course.
     *
     * This method validates the incoming request to ensure that the 'course_id' is provided,
     * is an integer, and exists in the 'courses' table. If validation fails, it returns a
     * JSON response with the validation errors and a 422 status code. If validation passes,
     * it calls the publish method on the course service object with the provided 'course_id'.
     *
     * @param \Illuminate\Http\Request $request The incoming request instance.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function publish(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer|exists:courses,id',
            'status' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
                'code'    => 422,
            ], 422);
        }

        return $this->courseServiceObj->publish($request->input('course_id'), $request->input('status'));
    }

    /**
     * Show Progress
     * Get the progress of course
     *
     * @param [string] $id
     *
     * user id
     * @return mixed
     */
    public function showProgress($id)
    {
        return $this->courseServiceObj->showProgress($id);
    }

    public function courseChapterWiseLession($course_id, $chapter_id)
    {
        $lessions = Lesson::where('course_id', $course_id)->where('chapter_id', $chapter_id)->get();

        // Add a flag to each lesson
        $lessions = $lessions->map(function ($lesson) {
            $lesson->is_set = !empty($lesson->video_url) || !empty($lesson->duration);
            return $lesson;
        });

        return $this->successResponse(true, 'All Courses', $lessions, 200);
    }

    public function getLesson($course_id, $chapter_id, $lesson_id)
    {
        try {
            // Fetch the lesson directly with the provided criteria
            $lesson = Lesson::where('course_id', $course_id)
                ->where('chapter_id', $chapter_id)
                ->where('id', $lesson_id)
                ->first();

            // Check if the lesson exists
            if (!$lesson) {
                return $this->failedResponse('Lesson not found for the given criteria.', null, 404);
            }

            // Return the lesson details
            return $this->successResponse(true, 'Lesson retrieved successfully.', [
                'lesson_id'    => $lesson->id,
                'course_id'    => $lesson->course_id,
                'chapter_id'   => $lesson->chapter_id,
                'lesson_order' => $lesson->lesson_order,
                'name'         => $lesson->name,
                'video_url'    => $lesson->video_url,
                'duration'     => $lesson->duration,
                'photo'        => $lesson->photo,
            ], 200);
        } catch (\Exception $e) {
            return $this->failedResponse('Failed to fetch lesson.', $e->getMessage(), 500);
        }
    }

    public function getTeacherStudents()
    {
        // Find the teacher

        $id = auth('api')->user()->id;

        $teacher = User::find($id);

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        // Get the teacher's students
        $students = $teacher->getStudents();

        $data = $students->map(function ($student) {
            return [
                'id'     => $student->id,
                'name'   => $student->name,
                'avatar' => $student->profile->avatar ?? 'files/images/user.png',
            ];
        });

        return $this->successResponse(true, 'Student List', $data, 200);
    }

    public function getCourseChaptersWithLessons($courseId)
    {
        $user = auth('api')->user();

        if (!$user || $user->role !== 'student') {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Fetch course with chapters and lessons
        $course = Course::with(['chapters.lessons'])->findOrFail($courseId);

        if (!CourseUser::where('user_id', $user->id)->where('course_id', $courseId)->where('access_granted', 1)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        // Get user progress for lessons
        $userLessonProgress = LessonUser::where('user_id', $user->id)
            ->whereIn('lesson_id', $course->chapters->flatMap->lessons->pluck('id'))
            ->get()
            ->keyBy('lesson_id'); // Key by lesson_id for easy lookup

        $courseData = [
            'chapters' => $course->chapters->map(function ($chapter) use ($userLessonProgress) {
                return [
                    'chapter_name' => $chapter->name,
                    'lessons'      => $chapter->lessons->map(function ($lesson) use ($userLessonProgress) {
                        $progress = $userLessonProgress[$lesson->id] ?? null;

                        return [
                            'lesson_name'  => $lesson->name,
                            'duration'     => (string) $lesson->duration, // Convert to string as in your sample
                            'video_url'    => $lesson->video_url,
                            'photo'        => str_replace('//', '/', $lesson->photo), // Fix double slashes
                            'is_completed' => (bool) optional($progress)->completed,
                            'watched_time' => optional($progress)->watched_time ?? 0
                        ];
                    }),
                ];
            }),
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Chapters and lessons with progress',
            'data'    => $courseData
        ]);
    }
}
