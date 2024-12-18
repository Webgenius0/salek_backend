<?php

namespace App\Http\Controllers\API;

use App\Models\Course;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreReviewRequest;

class ReviewController extends Controller
{
    use ApiResponse;
    
    public function store(StoreReviewRequest $request)
    {
        try {
            DB::beginTransaction();

            $user     = Auth::user();
            $reviewId = $request->input('reviewable_id');
            $rating   = $request->input('rating');
            $comment  = $request->input('comment');
            
            $review = Review::create([
                'user_id'         => $user->id,
                'reviewable_type' => Course::class,
                'reviewable_id'   => $reviewId,
                'rating'          => $rating,
                'comment'         => $comment,
            ]);

            DB::commit();
            return $this->successResponse(true, 'Review added successfully', $review, 201);

        } catch (\Exception $e) {
            DB::rollback();
            info($e);
            return response()->json([
                'status'  => false,
                'message' => 'Database Error',
                'error'   => $e->getMessage(),
                'code'    => 422
            ], 422);
        }
    }
}
