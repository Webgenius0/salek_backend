<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Services\CourseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public $courseServiceObj;

    public function __construct()
    {
        $this->courseServiceObj = new CourseService();
    }

    public function index()
    {
        $userId = Auth::id();

        $courses = Course::with(['chapters.lessons', 'category', 'creator'])
            ->whereDoesntHave('purchasers', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->get();

        $data = $courses->map(function($course){
            return [
                'course_id'    => $course->id,
                'course_title' => $course->name,
                'price'        => $course->price,
                'review'       => 4.9 . (232 . ' Reviews'),
                'total_chapter'  => $course->chapters->count(),
                'total_level' => $course->chapters->max('chapter_order'),
                'total_class'  => $course->total_class,
                'students'     => $course->purchasers->count(),
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
     * Popular Courses
     * Calculate popularity based on reviews,ratings,purchase history
     *
     * @return mixed
    */
    public function popularCourse()
    {
        return $this->courseServiceObj->popularCourse();
    }

    /**
     * course store method
     *
     * @param CourseStoreRequest $request
     * @return mixed
    */
    public function store(CourseStoreRequest $request)
    {
        $creatorId         = request()->user()->id;
        $name              = $request->input('course.name');
        $description       = $request->input('course.description');
        $category_id       = $request->input('course.category_id');
        $totalClass        = $request->input('course.total_class');
        $price             = $request->input('course.price');
        $chapters          = $request->chapters;
        $total_month       = $request->input('total_month');
        $additional_charge = $request->input('additional_charge');
        
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
            $chapters,
            (int) $total_month,
            (int) $additional_charge
        );
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

    public function currentCourse()
    {
        $user = request()->user();

        return $this->courseServiceObj->currentCourse($user);
    }
}
