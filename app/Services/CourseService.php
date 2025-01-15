<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Chapter;
use App\Models\CourseUser;
use App\Models\LessonUser;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use App\Models\StudentProgress;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use App\Notifications\StudentNotification;
use function PHPUnit\Framework\returnSelf;

class CourseService extends Service
{
    use ApiResponse;

    public $courseObj;

    public function __construct()
    {
        $this->courseObj = new Course();
    }

    /**
     * Retrieves a list of courses created by the authenticated user along with associated students and reviews.
     *
     * This method fetches courses created by the currently authenticated user, including related students and reviews.
     * It then maps the course data to include additional information such as the number of students, total classes,
     * and average rating.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success status, message, course data, and HTTP status code.
     */
    public function courseList()
    {
        $courses = Course::with(['students', 'reviews'])->where('created_by', Auth::id())
            ->latest()
            ->get();

        $data = $courses->map(function ($course) {
            $ratingCount   = $course->reviews->count();
            $ratingSum     = $course->reviews->sum('rating');
            $averageRating = $ratingCount > 0 ? number_format($ratingSum / $ratingCount, 1) : null;
            return [
                'course_id'      => $course->id,
                'course_title'   => $course->introduction_title,
                'cover_photo'    => $course->cover_photo,
                'tag'            => 'Online Course',
                'price'          => $course->price,
                'total_class'    => $course->total_class ?? 0,
                'students'       => $course->students->count() ?? 0,
                'rating_count'   => $ratingCount,
                'rating_sum'     => $ratingSum,
                'course_status'  => $course->status,
                'average_rating' => $averageRating ?? 0,
            ];
        });

        return $this->successResponse(true, 'All Courses', $data, 200);
    }

    /**
     * method for course create
     *
     * @param integer $creatorId
     * @param string $name
     * @param string $description
     * @param integer $category_id
     * @param integer $totalClass
     * @param integer $price
     * @param array $chapters
     * @param int $total_month
     * @param int $additional_charge
     * @return mixed
     */
    public function store(
        int $creatorId,
        string $name,
        string $description,
        int $category_id,
        int $totalClass,
        int $price,
        int $total_month,
        int $additional_charge,
        string $introduction_title,
        $cover_photo,
        $class_video
    ) {
        try {
            DB::beginTransaction();

            if ($cover_photo != null) {
                $cover_photo_name = time() . '.' . $cover_photo->getClientOriginalExtension();
                $cover_photo->move(public_path('uploads/course/introduction/cover_photo'), $cover_photo_name);
                $this->courseObj->cover_photo = 'uploads/course/introduction/cover_photo/' . $cover_photo_name;
            }

            if ($class_video != null) {
                $class_video_name = time() . '.' . $class_video->getClientOriginalExtension();
                $class_video->move(public_path('uploads/course/introduction/class_video'), $class_video_name);
                $this->courseObj->class_video = 'uploads/course/introduction/class_video/' . $class_video_name;
            }

            $this->courseObj->created_by         = $creatorId;
            $this->courseObj->name               = Str::title($name);
            $this->courseObj->slug               = Str::slug($name, '-');
            $this->courseObj->description        = $description;
            $this->courseObj->category_id        = $category_id;
            $this->courseObj->total_class        = $totalClass;
            $this->courseObj->price              = $price;
            $this->courseObj->total_month        = $total_month;
            $this->courseObj->additional_charge  = $additional_charge;
            $this->courseObj->introduction_title = $introduction_title;
            $this->courseObj->status             = 'publish';

            $res = $this->courseObj->save();

            DB::commit();
            if ($res) {
                $data = [
                    'course_id'          => $this->courseObj->id,
                    'course_name'        => $this->courseObj->name,
                    'course_slug'        => $this->courseObj->slug,
                    'total_class'        => $this->courseObj->total_class,
                    'course_price'       => $this->courseObj->price,
                    'introduction_title' => $this->courseObj->introduction_title,
                    'status'             => $this->courseObj->status,
                    'cover_photo'        => asset($this->courseObj->cover_photo),
                    'class_video'        => asset($this->courseObj->class_video),
                    'created_at'         => $this->courseObj->created_at,
                ];

                $this->notifyUsers($data);

                return $this->successResponse(true, 'Course created successfully.', $data, 201);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Database Error',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return response()->json([
                'success' => false,
                'error' => 'Database Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * method for chapter create
     *
     * @param integer $course_id
     * @param string $name
     * @param string $level_label
     * @param integer $chapter_order
     * @return mixed
     */
    public function chapterStore($course_id, string $name, string $level_label, $chapter_order)
    {
        try {
            DB::beginTransaction();

            $chapter = new Chapter();

            $chapter->course_id     = $course_id;
            $chapter->name          = $name;
            $chapter->slug          = Str::slug($name, '-');
            $chapter->level_label   = $level_label;
            $chapter->chapter_order = $chapter_order;

            $res = $chapter->save();
            DB::commit();
            if ($res) {
                return $this->successResponse(true, 'Chapter created successfully.', $chapter, 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedResponse('Failed to create chapter.', $e->getMessage(), 500);
        }
    }

    /**
     * method for lesson create
     *
     * @param integer $course_id
     * @param integer $chapter_id
     * @param string $name
     * @param integer $lesson_order
     * @param $video
     * @param integer $duration
     * @return mixed
     */
    public function lessonStore($course_id, $chapter_id, $video, $duration, $lesson_id, $photoPath = null)
    {
        try {
            DB::beginTransaction();

            $lesson = new Lesson();

            $videoPath = null;
            if ($video != null) {
                $fileName  = time() . '.' . $video->getClientOriginalExtension();
                $videoPath = 'uploads/course/lessons/videos/' . $fileName;
                $video->move(public_path('uploads/course/lessons/videos'), $fileName);
            }

            $lastLessonOrder = Lesson::where('chapter_id', $chapter_id)
                ->max('lesson_order');

            $lesson->chapter_id   = $chapter_id;
            $lesson->course_id    = $course_id;
            $lesson->lesson_order = $lastLessonOrder ? $lastLessonOrder + 1 : 1;
            $lesson->video_url    = $videoPath;
            $lesson->duration     = $duration;
            $lesson->photo        = $photoPath;

            $res = $lesson->save();
            DB::commit();
            if ($res) {
                return $this->successResponse(true, 'Lesson created successfully.', [
                    'lesson_id'    => $lesson_id,
                    'course_id'    => $lesson->course_id,
                    'chapter_id'   => $lesson->chapter_id,
                    'lesson_order' => $lesson->lesson_order,
                    'video_url'    => $lesson->video_url,
                    'duration'     => $lesson->duration,
                    'photo'     => $lesson->photo,
                ], 201);
            }
        }  catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedResponse('Failed to create lesson.', $e->getMessage(), 500);
        }
    }
    public function lessonStoreTwo($course_id, $chapter_id, string $name)
    {
        try {
            DB::beginTransaction();

            $lesson = new Lesson();


            $lastLessonOrder = Lesson::where('chapter_id', $chapter_id)
                ->max('lesson_order');

            $lesson->chapter_id   = $chapter_id;
            $lesson->course_id    = $course_id;
            $lesson->name         = $name;
            $lesson->lesson_order = $lastLessonOrder ? $lastLessonOrder + 1 : 1;

            $res = $lesson->save();
            DB::commit();
            if ($res) {
                return $this->successResponse(true, 'Lesson created successfully.', $lesson, 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return $this->failedResponse('Failed to create lesson.', $e->getMessage(), 500);
        }
    }

    /**
     * Course Service class
     * return popular class
     * father controller name coursecontroller
     *
     * @param [string] $id
     * @return mixed
     */
    public function popularCourse()
    {
        // Fetch popular courses for the authenticated teacher
        $popularCourses = Course::select(
            'courses.id',
            'courses.name',
            'courses.cover_photo',
            'courses.price',
            'courses.total_class'
        )
            ->where('courses.created_by', Auth::id()) // Only courses created by the authenticated teacher
            ->leftJoin('course_user', 'courses.id', '=', 'course_user.course_id')
            ->leftJoin('reviews', 'courses.id', '=', 'reviews.reviewable_id')
            ->selectRaw('
            COUNT(DISTINCT course_user.user_id) AS purchase_count,
            AVG(reviews.rating) AS avg_rating,
            COUNT(reviews.id) AS total_reviews,
            (COUNT(DISTINCT course_user.user_id) * 0.7 + AVG(reviews.rating) * 0.3) AS popularity_score
        ')
            ->groupBy('courses.id', 'courses.name', 'courses.cover_photo', 'courses.price', 'courses.total_class')
            ->orderBy('popularity_score', 'desc')
            ->take(10)
            ->get();



        $data = $popularCourses->map(function ($course) {
            return [
                'course_id'    => $course->id,
                'course_title' => $course->name,
                'thumbnail'    => $course->cover_photo,
                'price'        => $course->price,
                'review'       => number_format($course->avg_rating, 1) . ' (' . $course->total_reviews . ' Reviews)',
                'total_class'  => $course->total_class,
                'students'     => 1234,
            ];
        });

        return $this->successResponse(true, 'Popular Courses', $data, 200);
    }
    public function parentPopularCourse()
    {
        // Fetch popular courses from all teachers
    $popularCourses = Course::select(
        'courses.id',
        'courses.name',
        'courses.cover_photo',
        'courses.price',
        'courses.total_class'
    )
        ->leftJoin('course_user', 'courses.id', '=', 'course_user.course_id')
        ->leftJoin('reviews', 'courses.id', '=', 'reviews.reviewable_id')
        ->selectRaw('
            COUNT(DISTINCT course_user.user_id) AS purchase_count,
            AVG(reviews.rating) AS avg_rating,
            COUNT(reviews.id) AS total_reviews,
            (COUNT(DISTINCT course_user.user_id) * 0.7 + AVG(reviews.rating) * 0.3) AS popularity_score
        ')
        ->groupBy('courses.id', 'courses.name', 'courses.cover_photo', 'courses.price', 'courses.total_class')
        ->orderBy('popularity_score', 'desc')
        ->take(10) // Adjust this number to control how many courses are fetched
        ->get();



        $data = $popularCourses->map(function ($course) {
            return [
                'course_id'    => $course->id,
                'course_title' => $course->name,
                'thumbnail'    => $course->cover_photo,
                'price'        => $course->price,
                'review'       => number_format($course->avg_rating, 1) . ' (' . $course->total_reviews . ' Reviews)',
                'total_class'  => $course->total_class,
                'students'     => 1234,
            ];
        });

        return $this->successResponse(true, 'Popular Courses', $data, 200);
    }

    /**
     * Course Details method
     * Service Helper method
     *
     * @param [string] $id
     * @return mixed
     */
    public function show($id)
    {
        $course = Course::with(['chapters.lessons', 'category', 'creator'])->find($id);

        if (!$course) {
            return $this->failedResponse('Course not found', 404);
        }

        $chaptersData = [];

        foreach ($course->chapters as $index => $chapter) {
            $lessonData = [];

            foreach ($chapter->lessons as $lesson) {
                $lessonData[] = [
                    'lesson_name' => $lesson->name,
                    'duration'    => $lesson->duration,
                    'video_url'   => $lesson->video_url,
                ];
            }

            $chaptersData[] = [
                'chapter_name'  => $chapter->name,
                'lessons'       => $lessonData,
            ];
        }

        $data = [
            'course_id'      => $course->id,
            'course_title'   => $course->name,
            'course_thumbnail'   => $course->cover_photo,
            'course_video'   => $course->class_video,
            'description'    => $course->description,
            'total_duration' => $course->lessons->sum('duration'),
            'total_class'    => $course->total_class,
            'instructor'     => [
                'avatar'      => $course->creator->profile->avatar ?? null,
                'name'        => $course->creator->name,
            ],
            'chapters' => $chaptersData,
        ];

        return $this->successResponse(true, 'Course with chapters and lessons', $data, 200);
    }

    /**
     * Current Course method
     * Service Helper method
     *
     * @param [string] $user
     * @return mixed
     */
    public function currentCourse($user)
    {
        $courses = $user->purchasedCourses->map(function ($course) use ($user) {
            $courseProgress = StudentProgress::where('user_id', $user->id)->where('course_id', $course->id)->first();
            $totalProgress = $courseProgress ? ($courseProgress->course_progress + $courseProgress->homework_progress) : 0;

            $courseAvatar = $course->cover_photo ?? null;
            return [
                'course_id'       => $course->id,
                'course_title'    => $course->name,
                'course_avatar'   => $courseAvatar,
                'completion_rate' => round($totalProgress) . '%',
                'total_class'     => $course->total_class,
                'total_course'    => $user->purchasedCourses->count(),
                'achievements'    => 0,
                'students'        => $course->purchasers->count(),
            ];
        });

        return $this->successResponse(true, 'Current Courses', $courses, 200);
    }

    /**
     * Course Wise Chapter method
     * Service Helper method
     *
     * @param [string] $id
     * @return mixed
     */
    public function courseWiseChapter($id)
    {
        $course = Course::with('chapters')->find($id);

        if (!$course) {
            return $this->failedResponse('Course not found', 404);
        }

        $chaptersData = [];

        foreach ($course->chapters as $chapter) {
            $chaptersData[] = [
                'chapter_id'  => $chapter->id,
                'chapter_name'  => $chapter->name,
            ];
        }

        return $this->successResponse(true, 'Course with chapters and lessons', $chaptersData, 200);
    }

    /**
     * Retrieves the course details along with its chapters and lessons.
     *
     * This method takes a course object and maps its chapters and lessons into a structured array.
     * Each chapter includes its ID, name, and a list of lessons. Each lesson includes its ID, name,
     * video URL, and duration.
     *
     * @param \App\Models\Course $course The course object containing chapters and lessons.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the structured course data.
     */
    public function courseWithClass($course)
    {
        $data = $course->chapters->map(function ($chapter) {
            return [
                'chapter_id'   => $chapter->id,
                'chapter_name' => $chapter->name,
                'lessons' => $chapter->lessons->map(function ($lesson) {
                    return [
                        'lesson_id'   => $lesson->id,
                        'lesson_name' => $lesson->name,
                        'video_url'   => $lesson->video_url,
                        'duration'    => $lesson->duration,
                    ];
                }),
            ];
        });

        return $this->successResponse(true, 'All Classes', $data, 200);
    }

    /**
     * Course Achievement method
     * Service Helper method
     *
     * @param [string] $course
     * @return mixed
     */
    public function courseAchievement($course)
    {
        $data = "This module is under development";

        return $this->successResponse(true, 'Course Achievement', $data, 200);
    }

    /**
     * Retrieve the level progress of a course for the authenticated user.
     *
     * This method fetches a course along with its chapters and lessons, and calculates the progress
     * percentage for each level based on the number of completed lessons. It returns the progress
     * information for each level in the course.
     *
     * @param int $id The ID of the course.
     * @return \Illuminate\Http\JsonResponse The response containing the level progress information or an error message.
     */
    public function level($id)
    {
        $course = Course::with(['chapters.lessons', 'homework'])->find($id);

        if (!CourseUser::where('course_id', $course->id)->where('user_id', Auth::id())->exists()):
            return $this->failedResponse('You have no validity for this user.', 403);
        endif;

        if (!$course):
            return $this->failedResponse('Course not found.', 404);
        endif;

        $chapterLevels = $course->chapters->groupBy('chapter_order')->map(function ($chapters, $order) {
            $totalLessonsInLevel = $chapters->sum(fn($chapter) => $chapter->lessons->count());
            $completedLessonsInLevel = $chapters->sum(fn($chapter) => $chapter->lessons->filter(fn($lesson) => $lesson->lessonUser->contains('completed', true))->count());

            $progressPercentage = $totalLessonsInLevel > 0 ? round(($completedLessonsInLevel / $totalLessonsInLevel) * 100, 2) : 0;

            return [
                'level' => "Level $order",
                'progress' => $progressPercentage,
                'level_label' => $chapters->first()->level_label,
            ];
        });

        $totalLevels = $chapterLevels->count();
        $totalStudents = CourseUser::where('course_id', $course->id)->count();

        $courseInfo = [
            'course_name' => $course->name,
            'total_levels' => $totalLevels,
            'total_students' => $totalStudents,
            'levels' => $chapterLevels,
        ];

        return $this->successResponse(true, 'Course with level', $courseInfo, 200);
    }

    /**
     * Retrieve the ongoing courses for the authenticated user.
     *
     * This method fetches the courses purchased by the authenticated user and determines
     * the ongoing courses based on the lessons that have not been completed yet.
     *
     * @return \Illuminate\Http\JsonResponse
     * A JSON response containing the ongoing courses with the following structure:
     * - course_id: The ID of the course.
     * - course_name: The name of the course.
     * - incomplete_lessons: The count of incomplete lessons in the course.
     * - total_lessons: The total number of lessons in the course.
     * - lessons: A collection of incomplete lessons.
     */
    public function ongoingCourse()
    {
        $user = User::find(Auth::id());

        $userCourses = $user->purchasedCourses;

        $courseIds = $userCourses->pluck('id');

        $courses = Course::with(['chapters.lessons.lessonUser' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->whereIn('id', $courseIds)->get();

        $ongoingCourses = $courses->map(function ($course) {
            $courseLessons = $course->chapters->flatMap(function ($chapter) {
                return $chapter->lessons;
            });

            $incompleteLessons = $courseLessons->filter(function ($lesson) {
                return !$lesson->lessonUser->first()->completed;
            });

            if ($incompleteLessons->isNotEmpty()) {
                return [
                    'course_id'          => $course->id,
                    'course_name'        => $course->name,
                    'incomplete_lessons' => $incompleteLessons->count(),
                    'total_lessons'      => $courseLessons->count(),
                    'lessons'            => $incompleteLessons,
                ];
            }

            return null;
        })->filter();

        return $this->successResponse(true, 'Ongoing Courses', $ongoingCourses, 200);
    }

    /**
     * Complete the course for the authenticated user.
     *
     * This method retrieves the authenticated user and their purchased courses.
     * It then fetches the courses along with their chapters and lessons, including
     * the lesson completion status for the user.
     *
     * The method maps the courses to include the completion status of each lesson
     * and returns a response with the completed courses and their details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeCourse()
    {
        $user = User::find(Auth::id());

        $userCourses = $user->purchasedCourses;

        $courseIds = $userCourses->pluck('id');


        $courses = Course::with(['chapters.lessons.lessonUser' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->whereIn('id', $courseIds)->get();

        $coursesWithCompletionStatus = $courses->map(function ($course) use ($user) {
            $courseLessons = $course->chapters->flatMap(function ($chapter) {
                return $chapter->lessons;
            });

            $completedLessons = $courseLessons->map(function ($lesson) use ($user) {
                $lessonUser = $lesson->lessonUser->first();

                return [
                    'lesson_id'    => $lesson->id,
                    'lesson_name'  => $lesson->name,
                    'completed'    => $lessonUser ? $lessonUser->completed : false,
                    'completed_at' => $lessonUser ? $lessonUser->completed_at : null,
                ];
            });

            return [
                'course_id'         => $course->id,
                'course_name'       => $course->name,
                'completed_lessons' => $completedLessons->where('completed', true)->count(),
                'total_lessons'     => $completedLessons->count(),
                'lessons'           => $completedLessons,
            ];
        });

        return $this->successResponse(true, 'Completed Courses', $coursesWithCompletionStatus, 200);
    }

    /**
     * Publish or unpublish a course based on the given status.
     *
     * @param int $courseId The ID of the course to be published or unpublished.
     * @param int $status The status to set for the course (0 for published, 1 for unpublished).
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure.
     */
    public function publish($courseId, $status)
    {
        $course = Course::find($courseId);
        if ($course && $course->created_by == Auth::id()):
            $videoStatus = $status == 1 ? 'publish' : 'unpublish';

            $course->status     = $videoStatus;
            $course->updated_at = now();
            $course->save();

            return $this->successResponse(true, "Video $videoStatus successfully.", $course, 200);
        endif;
        return $this->failedResponse('Sorry: you are not creator of this course', 403);
    }

    /**
     * Display the progress of a course for the authenticated user.
     *
     * This method retrieves the course with its chapters and lessons by the given course ID.
     * It then calculates the weekly progress of the user based on the lessons they have completed.
     *
     * @param int $id The ID of the user.
     * @return \Illuminate\Http\JsonResponse The response containing the weekly progress data.
     */
    public function showProgress($id)
    {
        $lessonUserRecords = LessonUser::where('user_id', $id)->get();

        $weekProgress = $lessonUserRecords->groupBy(function ($record) {
            return Carbon::parse($record->updated_at)->format('Y-W');
        });

        $progressData = $weekProgress->map(function ($weekRecords, $week) {
            $totalLessonsInWeek     = $weekRecords->count();
            $completedLessonsInWeek = $weekRecords->where('completed', 1)->count();

            $progressPercentage = $totalLessonsInWeek > 0 ? round(($completedLessonsInWeek / $totalLessonsInWeek) * 100, 2) : 0;

            return [
                'week'              => $week,
                'progress'          => $progressPercentage,
                'total_lessons'     => $totalLessonsInWeek,
                'completed_lessons' => $completedLessonsInWeek,
            ];
        });

        return $this->successResponse(true, 'Weekly Progress', $progressData, 200);
    }
}
