<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display categories list with match counts and add form.
     */
    public function index(): View
    {
        $categories = Category::withCount('tournaments')
            ->orderBy('order')
            ->orderByDesc('id')
            ->get();

        $totalCount = $categories->count();
        $activeCount = $categories->where('is_active', true)->count();

        return view('admin.categories.index', compact('categories', 'totalCount', 'activeCount'));
    }

    /**
     * Store a new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'short_description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'title' => $request->input('title'),
            'image_path' => $imagePath,
            'short_description' => $request->input('short_description'),
            'order' => (int) $request->input('order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'short_description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'title' => $request->input('title'),
            'short_description' => $request->input('short_description'),
            'order' => (int) $request->input('order', 0),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Toggle category active status.
     */
    public function toggleActive(Category $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        return back()->with('success', 'Category status updated.');
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Unlink tournaments from this category so matches aren't broken
        $category->tournaments()->update(['category_id' => null]);

        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}

