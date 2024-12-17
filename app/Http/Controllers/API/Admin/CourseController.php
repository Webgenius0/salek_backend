<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Services\CourseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Models\Course;

class CourseController extends Controller
{
    public $courseServiceObj;

    public function __construct()
    {
        $this->courseServiceObj = new CourseService();
    }

    /**
     * course store method
     *
     * @param CourseStoreRequest $request
     * @return mixed
    */
    public function store(CourseStoreRequest $request)
    {
        $creatorId   = request()->user()->id;
        $name        = $request->input('course.name');
        $description = $request->input('course.description');
        $category_id = $request->input('course.category_id');
        $totalClass  = $request->input('course.total_class');
        $price       = $request->input('course.price');
        $chapters    = $request->chapters;
        
        if (is_null($chapters) || empty($chapters)) {
            return response()->json([
                'success' => false,
                'message' => 'Chapters data is missing or invalid.',
            ], 400);
        }

        return $this->courseServiceObj->store(
            (int) $creatorId,
            (string) $name,
            (string) $description,
            (int) $category_id,
            (int) $totalClass,
            (int) $price,
            $chapters
        );
    }

    public function show(Course $course)
    {
        return $course;
    }
}
