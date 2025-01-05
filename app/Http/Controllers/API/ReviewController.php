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
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    use ApiResponse;

    /**
     * Retrieves reviews for a specific course based on the type and ID provided.
     *
     * @param string $type The type of reviews to retrieve ('new', 'previous', or 'all').
     * @param int $id The ID of the course for which to retrieve reviews.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the reviews data or an error message.
     *
     * @throws \Exception If there is an error during the database query.
    */
    public function index($type, $courseId)
    {
        try {
            $reviews = Review::with(['user.profile', 'reviewable', 'reactions'])
                ->where('reviewable_type', Course::class)
                ->where('reviewable_id', $courseId);

            if ($type === 'new') {
                $reviews->where('created_at', '>=', now()->subWeek());
            } elseif ($type === 'previous') {
                $reviews->where('created_at', '<', now()->subWeek());
            } elseif ($type !== 'all') {
                return $this->failedResponse('Invalid type specified', 400);
            }

            $reviews = $reviews->get();

            $data = $reviews->map(function ($review) {
                return [
                    'review_id'   => $review->id,
                    'user_id'     => $review->user->id,
                    'user_name'   => $review->user->name,
                    'user_avatar' => $review->user->profile->avatar ?? 'files/images/user.png',
                    'rating'      => number_format($review->rating, 1),
                    'comment'     => $review->comment,
                    'react'       => $review->reactions->count() ?? 0,
                    'review_date' => $review->created_at->diffForHumans(),
                ];
            });

            return $this->successResponse(true, 'Reviews retrieved successfully', $data, 200);
        } catch (\Exception $e) {
            info($e);
            return $this->failedDBResponse('Database Error', $e->getMessage(), 422);
        }
    }

    /**
     * Store a newly created review in the database.
     *
     * @param  \App\Http\Requests\StoreReviewRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
    */
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

    /**
     * Store a reaction for a review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
    */
    public function reactStore(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'review_id' => 'required|exists:reviews,id',
                'reaction'  => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation Error',
                    'errors'  => $validator->errors(),
                    'code'    => 422
                ], 422);
            }

            $user     = Auth::user();
            $reviewId = $request->input('review_id');
            $reaction = $request->input('reaction');

            $review = Review::findOrFail($reviewId);

            $existingReaction = $review->reactions()->where('user_id', $user->id)->first();

            if ($existingReaction) {
                $existingReaction->delete();
                DB::commit();
                return $this->successResponse(true, 'Reaction removed successfully', null, 200);
            } else {
                $review->reactions()->create([
                    'user_id'  => $user->id,
                    'total_count' => 1,
                    'reaction' => $reaction,
                ]);

                DB::commit();
                return $this->successResponse(true, 'Reaction added successfully', null, 201);
            }
            $review->reactions()->updateOrCreate(
                ['user_id' => $user->id],
                ['reaction' => $reaction]
            );

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
