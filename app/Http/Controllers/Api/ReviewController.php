<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use App\Models\MenuItem;
class ReviewController extends Controller
{
    public function index()
    {
        return response()->json(
            Review::with(['user', 'menuItem'])->get()
        );
    }

    // public function store(StoreReviewRequest $request)
    // {
    //     $review = Review::create($request->validated());
    //     return response()->json($review, 201);
    // }
public function store(StoreReviewRequest $request)
{
    $data = $request->validated();

    $review = Review::create($data);

    return response()->json($review, 201);
}
    public function show($id)
    {
        return response()->json(
            Review::with(['user', 'menuItem'])->findOrFail($id)
        );
    }

   public function update(UpdateReviewRequest $request, $id)
{
    $review = Review::findOrFail($id);
    $review->update($request->validated());

    return response()->json($review);
}

    public function destroy($id)
    {
        Review::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}
