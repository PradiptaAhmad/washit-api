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
        $checkRating = Rating::where('order_id', $request->order_id)->where('user_id', $user->id)->first();
        if ($checkRating) {
            return response([
                'status' => 'failed',
                'message' => 'Rating already added',
            ], 400);
        }
        $rating = Rating::create([
            'rating' => $request->rating,
            'review' => $request->review,
            'order_id' => $request->order_id,
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
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        $rating = Rating::where('order_id', $request->order_id)->first();
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

    public function deleteRating($id)
    {
        $rating = Rating::where('id', $id)->first();
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

    
}
