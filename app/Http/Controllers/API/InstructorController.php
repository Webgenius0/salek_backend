<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Event;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Review;
use App\Models\Payment;
use App\Models\CourseUser;
use App\Models\LessonUser;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\StudentProgress;

use App\Http\Controllers\Controller;
use function PHPUnit\Framework\returnSelf;

class InstructorController extends Controller
{
    use ApiResponse;

    /**
    * Display a listing of instructors.
    *
    * This method retrieves all users with the role of 'teacher' along with their associated courses and profile.
    * It then maps the retrieved instructors to a simplified data structure containing the instructor's ID, avatar,
    * subject (class name), name, and total number of courses they teach.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function index()
    {
        $instructors = User::with(['courses', 'profile'])->where('role', 'teacher')->latest()->get();

        $data = $instructors->map(function($instructor){
            return [
                'id'            => $instructor->id,
                'avatar'        => $instructor->profile->avatar ?? null,
                'subject'       => $instructor->profile->class_name ?? null,
                'name'          => $instructor->name,
                'total_courses' => $instructor->courses->count(),
            ];
        });

        return $this->successResponse(true, 'Our Instructors', $data, 200);
    }

    /**
     * Display the teacher dashboard with an overview of students and events.
     *
     * This method retrieves all students with the role 'student' and filters out the new users
     * who have registered within the last 7 days. It also calculates the total revenue generated
     * from course purchases and event bookings. Additionally, it fetches all events with the status
     * 'complete' or 'upcoming' and categorizes them accordingly.
     *
     * The response data includes:
     * - Student overview: number of new students, total number of students, and total student revenue.
     * - Event overview: number of completed bookings, number of upcoming events, and total booking revenue.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function dashboard()
    {
        $allStudents = User::where('role', 'student')->get();

        $newUsers = $allStudents->filter(function($student){
            return $student->created_at >= now()->subDays(7);
        });

        $totalStudentRevenue = Payment::where('status', 'succeeded')->where('purchase_type', 'course')->sum('amount');
        $totalBookingRevenue = Payment::where('status', 'succeeded')->where('purchase_type', 'event')->sum('amount');

        $events = Event::where('status', 'complete')->orWhere('status', 'upcoming')->get();

        $totalBooking = $events->filter(function($event){
            return $event->status === 'complete';
        });

        $upcomingEvents = $events->filter(function($event){
            return $event->status === 'upcoming';
        });

        $data = [
            'student_overview' => [
                'new_student'   => count($newUsers),
                'total_student' => count($allStudents),
                'total_revenue' => $totalStudentRevenue,
            ],

            'event_overview' => [
                'total_booking'  => count($totalBooking),
                'upcoming_event' => count($upcomingEvents),
                'total_revenue'  => $totalBookingRevenue,
            ],
        ];

        return $this->successResponse(true, 'Teacher Dashboard', $data, 200);
    }

    /**
     * Display the specified teacher.
     *
     * @param  int  $id  The ID of the teacher to display.
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        // Retrieve the teacher details
        $teacher = User::with(['reviews'])
            ->where('id', $id)
            ->where('role', 'teacher')
            ->first();

        if (!$teacher) {
            return $this->failedResponse('Instructor not found', 404);
        }

        // Retrieve the teacher's courses with additional calculated columns
        $teacherCourses = Course::select(
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
            ->where('courses.created_by', $id) // Fetch only courses created by this teacher
            ->groupBy('courses.id', 'courses.name', 'courses.cover_photo', 'courses.price', 'courses.total_class')
            ->get();

        // Map the teacher's courses into a detailed structure
        $courses = $teacherCourses->map(function ($course) {

            $totalStudents =CourseUser::where('course_id', $course->id)
            ->count('user_id'); // Count distinct students

            return [
                'course_id'    => $course->id,
                'course_title' => $course->name,
                'thumbnail'    => $course->cover_photo,
                'price'        => $course->price,
                'review'       => number_format($course->avg_rating, 1) . ' (' . $course->total_reviews . ' Reviews)',
                'total_class'  => $course->total_class,
                'total_students' => $totalStudents,
            ];
        });

        // Combine all reviews for the teacher's courses
        $reviews = Review::select(
            'reviews.id as review_id',
            'reviews.rating',
            'reviews.comment',
            'users.name as reviewer_name',
            'users.avatar as reviewer_avatar',
            'reviews.reviewable_id as course_id'
        )
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->whereIn('reviews.reviewable_id', $teacherCourses->pluck('id')) // Reviews for the teacher's courses
            ->get();

        // Build the response
        $response = [
            'teacher' => [
                'id'    => $teacher->id,
                'name'  => $teacher->name,
                'avatar' => $teacher->profile->avatar ?? asset('files/images/user.png'),
            ],
            'courses' => $courses,
            'reviews' => $reviews->map(function ($review) {
                return [
                    'review_id'       => $review->review_id,
                    'course_id'       => $review->course_id,
                    'rating'          => $review->rating,
                    'comment'         => $review->comment,
                    'reviewer_name'   => $review->reviewer_name,
                    'reviewer_avatar' => $review->reviewer_avatar ?? asset('files/images/user.png'),
                ];
            }),
        ];

        return $this->successResponse(true, 'Teacher Profile Retrieved Successfully', $response, 200);
    }

    public function studentProfile($id)
    {
        $student = User::with(['profile'])->where('role', 'student')->where('id', $id)->first();
        if(!$student):
            return $this->failedResponse('Student not found', 404);
        endif;

        $lessonUser = LessonUser::where('user_id', $id)->where('updated_at', now())->latest()->first();

        $totalDuration = 10;

        $learnToday = 100;
        if($lessonUser):
            $lesson        = Lesson::with('course')->find($lessonUser->lesson_id);
            $chapters      = $lesson->course->chapters->load('lessons');

            $totalDuration = $chapters->flatMap(function ($chapter) {
                return $chapter->lessons;
            })->sum('duration');

            $learnToday = $lessonUser->watched_time;
        endif;

        $currentCourse = $student->purchasedCourses()->latest('purchased_at')->first();

        $currentCourseOverview = StudentProgress::where('course_id', $currentCourse->id)->where('user_id', $id)->first();

        $totalCourse = $student->purchasedCourses->count();

        $data = [
            'student_id'         => $student->id,
            'student_name'       => $student->name,
            'student_class'      => $student->profile->class_no ?? null,
            'student_class_name' => $student->profile->class_name ?? null,
            'avatar'             => $student->profile->avatar ?? asset('files/images/user.png'),
            'learned_today'      => round($learnToday / 60) . ' min',
            'total_time'         => $totalDuration . ' min',
            'completion_rate'    => ($currentCourseOverview->course_progress ?? 0) + ($currentCourseOverview->homework_progress ?? 0),
            'total_course'       => $totalCourse,
            'achievements'       => 6
        ];

        return $this->successResponse(true, 'Student Profile', $data, 200);
    }

    /**
     * Retrieve the list of published courses for a specific student.
     *
     * This function fetches the courses associated with a given student ID.
     * It first retrieves the course IDs linked to the student from the CourseUser model,
     * then fetches the corresponding courses from the Course model that have a status of 'publish'.
     * The courses are then mapped to an array format containing course details such as ID, name,
     * description, price, and status.
     *
     * @param int $id The ID of the student.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the success status, message,
     *                                       course data, and HTTP status code.
    */
    public function studentCourses($id)
    {
        $studentCourseIds = CourseUser::where('user_id', $id)->pluck('course_id');

        $courses = Course::whereIn('id', $studentCourseIds)->where('status', 'publish')->get();

        $data = $courses->map(function($course){
            return [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'description' => $course->description,
                'price' => $course->price,
                'status' => $course->status,
            ];
        });

        return $this->successResponse(true, 'Student Courses', $data, 200);
    }
}
