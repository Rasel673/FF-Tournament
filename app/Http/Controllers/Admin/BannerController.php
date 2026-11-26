<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    /**
     * Show the banners & pictures management page.
     */
    public function index(): View
    {
        $banners = Banner::orderBy('order')->orderByDesc('id')->get();
        $totalCount = $banners->count();
        $activeCount = $banners->where('is_active', true)->count();

        return view('admin.banners.index', [
            'banners' => $banners,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
        ]);
    }

    /**
     * Store a new picture / banner.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $path = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title' => $request->input('title'),
            'image_path' => $path,
            'link_url' => $request->input('link_url'),
            'is_active' => $request->boolean('is_active', true),
            'order' => (int) $request->input('order', 0),
        ]);

        return back()->with('success', 'Picture added successfully.');
    }

    /**
     * Update an existing picture / banner.
     */
    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'title' => 'nullable|string|max:100',
            'link_url' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'title' => $request->input('title'),
            'link_url' => $request->input('link_url'),
            'is_active' => $request->boolean('is_active'),
            'order' => (int) $request->input('order', 0),
        ];

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_path);
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return back()->with('success', 'Picture updated successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Banner $banner): RedirectResponse
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return back()->with('success', 'Picture status updated.');
    }

    /**
     * Delete a picture / banner and its file.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();

        return back()->with('success', 'Picture deleted successfully.');
    }
}
