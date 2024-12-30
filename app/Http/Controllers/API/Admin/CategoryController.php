<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public $categoryServiceObj;

    public function __construct()
    {
        $this->categoryServiceObj = new CategoryService();
    }

    public function index()
    {
        $categories = Category::select('id', 'name','status')->where('status', 'active')->get()->toArray();

        if(!empty($categories)){
            return $this->categoryServiceObj->index($categories);
        }

        return response()->json(['status' => false, 'message' => 'Data not found']);
    }

    public function store(CategoryStoreRequest $request)
    {
        $name      = $request->input('name');
        $createdBy = request()->user()->id;
        
        return $this->categoryServiceObj->store($name, $createdBy);
    }

    public function update(CategoryUpdateRequest $request)
    {
        $id        = $request->input('id');
        $name      = $request->input('name');
        $status    = $request->input('status');
        $updatedBy = request()->user()->id;
        
        return $this->categoryServiceObj->update($id,$name, $status, $updatedBy);
    }
}
