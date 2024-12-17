<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Services\CourseService;

class CourseController extends Controller
{
    public $courseServiceObj;

    public function __construct()
    {
        $this->courseServiceObj = new CourseService();
    }

    public function store(CourseStoreRequest $request)
    {
        $creatorId   = request()->user()->id;
        $name        = $request->input('name');
        $description = $request->input('description');
        $category_id = $request->input('category_id');
        $totalClass  = $request->input('total_class');
        $price       = $request->input('price');

        return $this->courseServiceObj->store(
            (int) $creatorId,
            (string) $name,
            (string) $description,
            (int) $category_id,
            (int) $totalClass,
            (int) $price,
        );
    }
}
