<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Users appear successfully',
            'result' => Review::where("rating", ">", 4)->get(),
            'error' => null,
        ], 200)->header('Accept', 'application/json');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'product_id' => 'required|numeric',
            'rating' => 'required|numeric',
            'comment' => 'required',
        ]);

        $res = Review::create($request->post());

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Review created successfully',
                'result' => $res,
                'error' => null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Faied to create a new review',
            'result' => null,
            'error' => null,
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        return response()->json([
            'success' => true,
            'message' => 'Review appear successfully',
            'result' => $review,
            'error' => null,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'product_id' => 'required|numeric',
            'rating' => 'required|numeric',
            'comment' => 'required',
        ]);

        $review->fill($request->post())->update();
        $res = $review->save();

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'result' => Review::find($review->id),
                'error' => null,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Faied to update the review',
            'result' => null,
            'error' => null,
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $res = $review->delete();
        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully',
                'result' => $review,
                'error' => null,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Faied to delete the review',
            'result' => null,
            'error' => null,
        ], 400);
    }
}
