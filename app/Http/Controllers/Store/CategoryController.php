<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Список категорий (сгруппированный: родительские → подкатегории).
     */
    public function index()
    {
        $rootCategories = Category::root()->with('children')->get();
        return view('store.categories.index', compact('rootCategories'));
    }

    /**
     * Форма создания категории.
     */
    public function create()
    {
        // Все родительские категории, чтобы можно было выбрать родителя (для подкатегории)
        $rootCategories = Category::root()->get();
        return view('store.categories.create', compact('rootCategories'));
    }

    /**
     * Сохранение новой категории.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return redirect()->route('store.categories.index')
            ->with('success', 'Категория добавлена');
    }

    /**
     * Форма редактирования категории.
     */
    public function edit(Category $category)
    {
        // Нельзя выбрать себя или своих потомков в качестве родителя (просто исключим текущую)
        $rootCategories = Category::root()->where('id', '!=', $category->id)->get();
        return view('store.categories.edit', compact('category', 'rootCategories'));
    }

    /**
     * Обновление категории.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return redirect()->route('store.categories.index')
            ->with('success', 'Категория обновлена');
    }

    /**
     * Удаление категории.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Категория удалена');
    }
}