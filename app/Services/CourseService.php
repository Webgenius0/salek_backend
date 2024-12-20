<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Chapter;
use App\Traits\ApiResponse;
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
        array $chapters,
        int $total_month,
        int $additional_charge
    )
    {
        try {
            DB::beginTransaction();

            $chaptersPerLevel = 2;

            $this->courseObj->created_by        = $creatorId;
            $this->courseObj->name              = Str::title($name);
            $this->courseObj->slug              = Str::slug($name, '-');
            $this->courseObj->description       = $description;
            $this->courseObj->category_id       = $category_id;
            $this->courseObj->total_class       = $totalClass;
            $this->courseObj->price             = $price;
            $this->courseObj->total_month       = $total_month;
            $this->courseObj->additional_charge = $additional_charge;
            $this->courseObj->status            = 'publish';

            $res = $this->courseObj->save();

            DB::commit();
            if($res){

                foreach ($chapters as $chapterKey => $chapterData) {
                    $chapter             = new Chapter();

                    $level = floor($chapterKey / $chaptersPerLevel) + 1;

                    $chapter->course_id  = $this->courseObj->id;
                    $chapter->name       = $chapterData['chapter_name'];
                    $chapter->chapter_order = $level;
                    
                    $chapter->save();

                    foreach ($chapterData['lessons'] as $lessonKey => $lessonData) {
                        $imagePath = null;
                        if (isset($lessonData['image_url']) && $lessonData['image_url']) {
                            $fileName  = time() . '.' . $lessonData['image_url']->getClientOriginalExtension();
                            $imagePath = 'uploads/course/lessons/thumbnail/' . $fileName;
                            $lessonData['image_url']->move(public_path('uploads/course/lessons/thumbnail'), $fileName);
                        }

                        $videoPath = null;
                        if (isset($lessonData['video_url']) && $lessonData['video_url']) {
                            $fileName  = time() . '.' . $lessonData['video_url']->getClientOriginalExtension();
                            $videoPath = 'uploads/course/lessons/videos/' . $fileName;
                            $lessonData['video_url']->move(public_path('uploads/course/lessons/videos'), $fileName);
                        }

                        $lesson             = new Lesson();
                        $lesson->chapter_id = $chapter->id;
                        $lesson->course_id  = $this->courseObj->id;
                        $lesson->name       = $lessonData['lesson_name'];
                        $lesson->duration   = $lessonData['duration'];
                        $lesson->image_url  = $imagePath;
                        $lesson->video_url  = $videoPath;
                        
                        $lesson->save();
                    }
                }

                DB::commit();
                return $this->successResponse(true, 'Course and chapters created successfully.', $this->courseObj, 201);
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
                    'image_url'   => $lesson->image_url,
                ];
            }

            $chaptersData[] = [
                'chapter_name'  => $chapter->name,
                'chapter_index' => $index + 1,
                'lessons'       => $lessonData,
            ];
        }

        $data = [
            'course_id'      => $course->id,
            'course_title'   => $course->name,
            'description'    => $course->description,
            'total_duration' => $course->lessons->sum('duration'),
            'total_class'    => $course->total_class,
            'instructor'     => [
                'avatar'      => $course->creator->avatar,
                'name'        => $course->creator->name,
                'designation' => $course->creator->designation,
            ],
            'chapters' => $chaptersData,
        ];

        return $this->successResponse(true, 'Course with chapters and lessons', $data, 200);
    }

    public function currentCourse($user)
    {
        $courses = $user->purchasedCourses->map(function($course) use ($user){

            $totalLessons = $course->lessons->count();

            // Count the completed lessons
            $completedLessons = $course->lessons->filter(function($lesson) use ($user) {
                // Check if the user is associated with the lesson and then check if it's completed
                $userLesson = $lesson->users->where('user_id', $user->id)->first();
                return $userLesson && $userLesson->pivot->completed; // Check if $userLesson is not null
            })->count();

            // Calculate completion rate
            $completionRate = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            $courseAvatar = $course->lessons->first()->image_url ?? null;
            return [
                'course_id'       => $course->id,
                'course_title'    => $course->name,
                'course_avatar'   => $courseAvatar,
                'completion_rate' => $completionRate . '%',
                'total_class'     => $course->total_class,
                'students'        => $course->purchasers->count(),
            ];
        });

        return $this->successResponse(true, 'Current Courses', $courses, 200);
    }
}
