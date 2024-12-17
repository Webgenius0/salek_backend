<?php

namespace App\Services;

use App\Models\Course;
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
     * @return mixed
    */
    public function store(
        int $creatorId,
        string $name,
        string $description,
        int $category_id,
        int $totalClass,
        int $price
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
                return 'Here add chapter wise class';
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
}
