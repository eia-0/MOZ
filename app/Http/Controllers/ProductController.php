<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->query('store_id');
        $store = $storeId ? Store::find($storeId) : Store::first();

        $categoryId = $request->query('category_id');
        $productsQuery = Product::where('is_available', true);

        if ($store) {
            $productsQuery->where('store_id', $store->id);
        }

        // Если выбрана категория – покажем страницу категории (подкатегории + товары)
        if ($categoryId) {
            $category = Category::with('children')->findOrFail($categoryId);
            $subCategoryIds = $category->children()->pluck('id')->push($category->id);
            $products = $productsQuery->whereIn('category_id', $subCategoryIds)->get();

            // Группируем товары по подкатегориям
            $groupedProducts = [];
            foreach ($category->children as $child) {
                $groupedProducts[$child->name] = $products->where('category_id', $child->id);
            }
            // Товары, которые напрямую в родительской категории (без подкатегории)
            $directProducts = $products->where('category_id', $category->id);
            if ($directProducts->isNotEmpty()) {
                $groupedProducts[$category->name] = $directProducts;
            }

            $cart = session()->get('cart', []);
            $isOpen = $store ? $store->isOpenNow() : false;

            return view('products.category', compact('category', 'groupedProducts', 'store', 'isOpen', 'cart'));
        }

        // Обычная главная страница
        $products = $productsQuery->with('store', 'category')->paginate(12);
        $cart = session()->get('cart', []);

        $activeOrders = collect();
        if (Auth::check() && Auth::user()->isCustomer()) {
            $activeOrders = Order::where('customer_id', Auth::id())
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->latest()
                ->get();
        }

        $categories = collect();
        if ($store) {
            $categories = Category::root()->get();
        }

        $isOpen = $store ? $store->isOpenNow() : false;

        return view('products.index', compact(
            'products', 'cart', 'activeOrders', 'store', 'isOpen', 'categories', 'categoryId'
        ));
    }

    public function show(Product $product)
    {
        $product->load('store');
        $cart = session()->get('cart', []);
        return view('products.show', compact('product', 'cart'));
    }
}