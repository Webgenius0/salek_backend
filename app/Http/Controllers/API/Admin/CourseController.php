<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Services\CourseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Models\Course;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class CourseController extends Controller
{
    public $courseServiceObj;

    public function __construct()
    {
        $this->courseServiceObj = new CourseService();
    }

    public function index()
    {
        $courses = Course::with(['chapters.lessons', 'category', 'creator'])->latest()->get();

        $data = $courses->map(function($course){
            return [
                'course_id'    => $course->id,
                'course_title' => $course->title,
                'price'        => $course->price,
                'review'       => 4.9 . (232 . ' Reviews'),
                'total_class'  => $course->total_class,
                'students'     => 1,                        234,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'All Courses',
            'data'    => $data,
            'code'    => 200,
        ], 200);
    }

    public function popularCourse()
    {
        $courses = Course::with(['chapters.lessons', 'category', 'creator'])->latest()->get();

        $data = $courses->map(function($course){
            return [
                'course_id'    => $course->id,
                'course_title' => $course->title,
                'price'        => $course->price,
                'review'       => 4.9 . (232 . ' Reviews'),
                'total_class'  => $course->total_class,
                'students'     => 1,                        234,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Popular Courses',
            'data'    => $data,
            'code'    => 200,
        ], 200);
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

    public function show($id)
    {
        return $this->courseServiceObj->show($id);
    }

    public function enroll(Request $request, $id)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = PaymentIntent::create([
            'amount' => 1000,
            'currency' => 'usd',
            'metadata' => ['integration_check' => 'accept_a_payment'],
        ]);

        return $paymentIntent;
    }
}
