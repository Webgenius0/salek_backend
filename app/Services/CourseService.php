<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Chapter;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CourseService extends Service
{
    use ApiResponse;
    
    public $courseObj;

    public function __construct()
    {
        $this->courseObj = new Course();
    }

    public function courseList()
    {
        $courses = Course::where('created_by', Auth::id())
            ->latest()
            ->get();

        $data = $courses->map(function($course){
            return [
                'course_id'    => $course->id,
                'course_title' => $course->name,
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
    )
    {
        try {
            DB::beginTransaction();

            if($cover_photo != null){
                $cover_photo_name = time() . '.' . $cover_photo->getClientOriginalExtension();
                $cover_photo->move(public_path('uploads/course/introduction/cover_photo'), $cover_photo_name);
                $this->courseObj->cover_photo = 'uploads/course/introduction/cover_photo/' . $cover_photo_name;
            }

            if($class_video != null){
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
            if($res){
                return $this->successResponse(true, 'Course created successfully.', $this->courseObj, 201);
            }
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Database Error',
                'message' => $e->getMessage(),
            ], 500);
        } 
        catch (\Exception $e) {
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
            if($res){
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
    public function lessonStore($course_id, $chapter_id, string $name, $lesson_order, $video, $duration)
    {
        try {
            DB::beginTransaction();

            $lesson = new Lesson();

            $videoPath = null;
            if($video != null){
                $fileName  = time() . '.' . $video->getClientOriginalExtension();
                $videoPath = 'uploads/course/lessons/videos/' . $fileName;
                $video->move(public_path('uploads/course/lessons/videos'), $fileName);
            }

            $lesson->chapter_id = $chapter_id;
            $lesson->course_id  = $course_id;
            $lesson->name       = $name;
            $lesson->lesson_order = $lesson_order;
            $lesson->video_url  = $videoPath;
            $lesson->duration   = $duration;

            $res = $lesson->save();
            DB::commit();
            if($res){
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
        $popularCourses = Course::select('courses.*')
                ->leftJoin('course_user', 'courses.id', '=', 'course_user.course_id')
                ->leftJoin('reviews', 'courses.id', '=', 'reviews.reviewable_id')
                ->selectRaw('
                    COUNT(DISTINCT course_user.user_id) AS purchase_count,
                    AVG(reviews.rating) AS avg_rating,
                    COUNT(reviews.id) AS total_reviews,
                    (COUNT(DISTINCT course_user.user_id) * 0.7 + AVG(reviews.rating) * 0.3) AS popularity_score
                ')
                ->groupBy('courses.id')
                ->orderBy('popularity_score', 'desc')
                ->take(10)
                ->get();
        
            
        $data = $popularCourses->map(function($course){
            return [
                'course_id'    => $course->id,
                'course_title' => $course->name,
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
                'avatar'      => $course->creator->avatar,
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
        $courses = $user->purchasedCourses->map(function($course) use ($user){

            $totalLessons = $course->lessons->count();

            $completedLessons = $course->lessons->filter(function($lesson) use ($user) {
                $userLesson = $lesson->users->where('user_id', $user->id)->first();
                return $userLesson && $userLesson->pivot->completed;
            })->count();

            $completionRate = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            $courseAvatar = $course->cover_photo ?? null;
            return [
                'course_id'       => $course->id,
                'course_title'    => $course->name,
                'course_avatar'   => $courseAvatar,
                'completion_rate' => $completionRate . '%',
                'total_class'     => $course->total_class,
                'total_course'    => $course->count(),
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
     * Course With Class method
     * Service Helper method
     *
     * @param [string] $course
     * @return mixed
    */
    public function courseWithClass($course)
    {
        $data = $course->chapters->map(function($chapter){
            return [
                'chapter_id'   => $chapter->id,
                'chapter_name' => $chapter->name,
                'level'        => $chapter->level_label,
                'total_lesson' => $chapter->lessons->count(),
                'lessons' => $chapter->lessons->map(function($lesson){
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
     * Ongoing Course method
     * Service Helper method
     *
     * @return mixed
    */
    public function ongoingCourse()
    {
        $data = 'This module is under development';

        return $this->successResponse(true, 'Ongoing Courses', $data, 200);
    }

    /**
     * Complete Course method
     * Service Helper method
     *
     * @return mixed
    */
    public function completeCourse()
    {
        $data = 'This module is under development';

        return $this->successResponse(true, 'Completed Courses', $data, 200);
    }
}
