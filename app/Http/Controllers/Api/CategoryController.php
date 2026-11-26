<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TournamentResource;
use App\Models\Category;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all active categories with match counts.
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount(['tournaments' => fn ($q) => $q->whereIn('status', ['active', 'upcoming'])])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Get single category details.
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::where('is_active', true)
            ->withCount('tournaments')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Get matches / tournaments under a specific category.
     */
    public function tournaments(Request $request, int $id)
    {
        $category = Category::where('is_active', true)->findOrFail($id);

        $userId = $request->user()?->id;

        $query = Tournament::where('category_id', $category->id)->orderByDesc('start_time');

        if ($userId) {
            $query = Tournament::withUserState($userId)->where('category_id', $category->id)->orderByDesc('start_time');
        }

        if (in_array($request->status, ['active', 'upcoming', 'completed'], true)) {
            $query->where('status', $request->status);
        }

        return TournamentResource::collection($query->paginate(20));
    }
}

