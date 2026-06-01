<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->paginate(20);
        return view('store.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::root()->get();
        return view('store.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        Category::create($validated);
        return redirect()->route('store.categories.index')->with('success', 'Категория создана');
    }

    public function edit(Category $category)
    {
        $parents = Category::root()->where('id', '!=', $category->id)->get();
        return view('store.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        if (!empty($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return back()->with('error', 'Нельзя назначить категорию родителем самой себя');
        }
        $category->update($validated);
        return redirect()->route('store.categories.index')->with('success', 'Категория обновлена');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Категория удалена');
    }
}