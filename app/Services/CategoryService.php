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

    public function update($id,$name, $status, $updatedBy)
    {
        try {
            DB::beginTransaction();

            $category = $this->categoryObj->where('id', $id)->first();
            
            if(!$category){
                return $this->failedResponse('Category not found', 404);
            }

            $category->name       = Str::title($name);
            $category->slug       = Str::slug($name, '-');
            $category->updated_by = $updatedBy;
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
