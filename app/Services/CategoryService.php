<?php

namespace App\Services;

use App\Models\Category;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategoryService extends Service
{
    use ApiResponse;
    
    public $categoryObj;

    public function __construct()
    {
        $this->categoryObj = new Category();
    }

    public function index(array $categories)
    {
        return $this->successResponse(true, 'Category List', $categories, 200);
    }

    public function store($name, $createdBy)
    {
        try {
            DB::beginTransaction();

            $this->categoryObj->name = Str::title($name);
            $this->categoryObj->slug = Str::slug($name, '-');
            $this->categoryObj->created_by = $createdBy;
            $this->categoryObj->status = 'active';

            $res = $this->categoryObj->save();

            DB::commit();
            if($res){
                return $this->successResponse(true, 'Category created successfully', $this->categoryObj, 201);
            }

            return $this->failedResponse('Category creation failed', 400);
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }

    public function show($id)
    {
        $category = Category::find($id);

        if(!$category):
            return $this->failedResponse('Category not found', 404);
        endif;

        $data = [
            'category_id'   => $category->id,
            'category_name' => $category->name,
            'status'        => $category->status,
        ];

        return $this->successResponse(true, 'Category Details', $data, 200);
    }

    public function update($id,$name, $status)
    {
        try {
            DB::beginTransaction();

            $category = $this->categoryObj->where('id', $id)->first();
            
            if(!$category){
                return $this->failedResponse('Category not found', 404);
            }

            $category->name       = Str::title($name);
            $category->slug       = Str::slug($name, '-');
            $category->status     = $status;
            $category->updated_at = Carbon::now();

            $res = $category->save();

            DB::commit();
            if($res){
                return $this->successResponse(true, 'Category updated successfully', $category, 201);
            }

            return $this->failedResponse('Category update failed', 400);
        } catch (\Exception $e) {
            DB::rollback();
            info($e);
        }
    }
}
