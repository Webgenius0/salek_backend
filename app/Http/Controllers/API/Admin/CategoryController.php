<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;

class CategoryController extends Controller
{
    public $categoryServiceObj;

    public function __construct()
    {
        $this->categoryServiceObj = new CategoryService();
    }

    /**
     * Display a listing of the active categories.
     *
     * This method retrieves all categories with 'id', 'name', and 'status' fields
     * where the status is 'active'. If active categories are found, it passes them
     * to the category service's index method. If no active categories are found,
     * it returns a JSON response indicating that no data was found.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function index()
    {
        $categories = Category::select('id', 'name','status')->where('status', 'active')->where('created_by', Auth::id())->get()->toArray();

        if(!empty($categories)){
            return $this->categoryServiceObj->index($categories);
        }

        return response()->json(['status' => false, 'message' => 'Data not found']);
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \App\Http\Requests\CategoryStoreRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function store(CategoryStoreRequest $request)
    {
        $name      = $request->input('name');
        $createdBy = request()->user()->id;
        
        return $this->categoryServiceObj->store($name, $createdBy);
    }

    public function show($id)
    {
        return $this->categoryServiceObj->show($id);
    }

    /**
     * Update the specified category.
     *
     * @param CategoryUpdateRequest $request The request object containing the category update data.
     * @return mixed The result of the category update operation.
    */
    public function update(CategoryUpdateRequest $request)
    {
        $id        = $request->input('id');
        $name      = $request->input('name');
        $status    = $request->input('status');
        
        return $this->categoryServiceObj->update($id,$name, $status);
    }
}
