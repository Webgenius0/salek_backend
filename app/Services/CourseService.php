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
     * @return mixed
    */
    public function store(
        int $creatorId,
        string $name,
        string $description,
        int $category_id,
        int $totalClass,
        int $price,
        array $chapters
    )
    {
        try {
            DB::beginTransaction();

            $this->courseObj->created_by  = $creatorId;
            $this->courseObj->name        = Str::title($name);
            $this->courseObj->slug        = Str::slug($name, '-');
            $this->courseObj->description = $description;
            $this->courseObj->category_id = $category_id;
            $this->courseObj->total_class = $totalClass;
            $this->courseObj->price       = $price;
            $this->courseObj->status      = 'publish';

            $res = $this->courseObj->save();

            DB::commit();
            if($res){

                foreach ($chapters as $chapterKey => $chapterData) {
                    $chapter             = new Chapter();
                    $chapter->course_id  = $this->courseObj->id;
                    $chapter->name       = $chapterData['chapter_name'];
                    
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
            'instructor'     => $course->creator->name,
            'chapters'       => $chaptersData,
        ];

        return $this->successResponse(true, 'Course with chapters and lessons', $data, 200);
    }
}
