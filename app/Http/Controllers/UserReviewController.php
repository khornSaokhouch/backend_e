<?php

namespace App\Http\Controllers;

use App\Models\UserReview;
use Illuminate\Http\Request;

class UserReviewController extends Controller
{
    // List all reviews for authenticated user
    public function index()
    {
        $userId = auth()->id();

        $reviews = UserReview::where('user_id', $userId)->get();

        return response()->json($reviews);
    }

    // Show a single review by id for authenticated user
    public function show($id)
    {
        $userId = auth()->id();

        $review = UserReview::where('id', $id)->where('user_id', $userId)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        return response()->json($review);
    }

    // Create a new review
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'order_product_id' => 'required|exists:order_lines,id',
            'review_text' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $review = UserReview::create([
            'user_id' => $userId,
            'order_product_id' => $validated['order_product_id'],
            'review_text' => $validated['review_text'] ?? null,
            'rating' => $validated['rating'],
        ]);

        return response()->json($review, 201);
    }


    public function update(Request $request, $id)
{
    $userId = auth()->id();

    $review = UserReview::where('id', $id)->where('user_id', $userId)->first();

    if (!$review) {
        return response()->json(['message' => 'Review not found'], 404);
    }

    $validated = $request->validate([
        'review_text' => 'nullable|string',
        'rating' => 'nullable|integer|min:1|max:5',
    ]);

    $review->update($validated);

    return response()->json($review);
}


    // Delete a review by id
    public function destroy($id)
    {
        $userId = auth()->id();

        $review = UserReview::where('id', $id)->where('user_id', $userId)->first();

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
