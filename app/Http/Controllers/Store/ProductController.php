<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    private function getStore()
    {
        return Auth::user()->store;
    }

    private function ensureOwnership(Product $product)
    {
        $store = $this->getStore();
        if (!$store || $product->store_id !== $store->id) {
            abort(403, 'Нет доступа');
        }
    }

    public function index()
    {
        $store = $this->getStore();
        $products = $store->products()->with('category')->latest()->paginate(10);
        return view('store.products.index', compact('store', 'products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('store.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $store = $this->getStore();
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric',
            'weight'        => 'required|integer',
            'category_id'   => 'required|exists:categories,id',
            'image'         => 'nullable|image|max:2048',
            'is_available'  => 'boolean',
        ]);

        $validated['store_id'] = $store->id;
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);
        return redirect()->route('store.products.index')->with('success', 'Товар добавлен');
    }

    public function edit(Product $product)
    {
        $this->ensureOwnership($product);
        $categories = Category::all();
        return view('store.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureOwnership($product);
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric',
            'weight'        => 'required|integer',
            'category_id'   => 'required|exists:categories,id',
            'image'         => 'nullable|image|max:2048',
            'is_available'  => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        return redirect()->route('store.products.index')->with('success', 'Товар обновлён');
    }

    public function destroy(Product $product)
    {
        $this->ensureOwnership($product);
        $product->delete();
        return back()->with('success', 'Товар удалён');
    }
}