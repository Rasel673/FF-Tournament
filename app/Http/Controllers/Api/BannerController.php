<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Get all active pictures / banners for slider or list.
     */
    public function index(): JsonResponse
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('order')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BannerResource::collection($banners),
        ]);
    }

    /**
     * Get a single picture / banner.
     */
    public function show(Banner $banner): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new BannerResource($banner),
        ]);
    }

    /**
     * Upload a picture / banner via API.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $path = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'title' => $request->input('title'),
            'image_path' => $path,
            'link_url' => $request->input('link_url'),
            'is_active' => true,
            'order' => (int) $request->input('order', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Picture uploaded successfully',
            'data' => new BannerResource($banner),
        ], 201);
    }
}
