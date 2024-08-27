<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddRatingRequest;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function addRating(AddRatingRequest $request)
    {
        $request->validated();
        $user = auth()->user();
        $checkRating = Rating::where('history_id', $request->history_id)->where('user_id', $user->id)->first();
        if ($checkRating) {
            return response([
                'status' => 'failed',
                'message' => 'Rating already added',
            ], 400);
        }
        $rating = Rating::create([
            'rating' => $request->rating,
            'review' => $request->review,
            'history' => $request->history_id,
            'user_id' => $user->id,
        ]);

        return response([
            'status' => 'success',
            'message' => 'Rating added successfully',
            'rating' => $rating,
        ], 201);
    }

    public function getRating(Request $request)
    {
        $request->validate(['history_id' => 'required|integer|exists:histories,id',
        ]);
        $rating = Rating::where('history_id', $request->history_id)->first();
        if ($rating == null) {
            return response([
                'status' => 'failed',
                'message' => 'Rating not found',
            ], 404);
        }
        return response([
            'status' => 'success',
            'message' => 'Get all rating successfully',
            'rating' => $rating,
        ], 200);
    }

    public function deleteRating(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:ratings,id',
        ]);
        $rating = Rating::where('id', $request->id)->first();
        if ($rating == null) {
            return response([
                'status' => 'failed',
                'message' => 'Rating not found',
            ], 404);
        }
        $rating->delete();
        return response([
            'status' => 'success',
            'message' => 'Rating deleted successfully',
        ], 200);
    }

    public function getRatingSummary()
    {
        $rating = Rating::all();
        if ($rating->isEmpty()) {
            return response([
                'status' => 'failed',
                'message' => 'Rating not found',
            ], 404);
        }
        return response([
            'status' => 'success',
            'message' => 'Get summary rating successfully',
            'average_rating' => round($rating->avg('rating'), 1),
            'total_review' => $rating->count(),
        ], 200);
    }


    public function getAllRatings()
    {
        $rating = Rating::all();
        if ($rating == null) {
            return response([
                'status' => 'failed',
                'message' => 'Rating is empty',
            ], 404);
        }
        return response([
            'status' => 'success',
            'message' => 'Get all rating successfully',
            'rating' => $rating,
        ], 200);
    }

}
