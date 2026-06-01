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
        $categoryId = $request->query('category_id');

        $store = $storeId ? Store::find($storeId) : Store::first();

        // Только корневые категории для фильтра
        $categories = Category::root()->get();

        $products = Product::when($store, fn($q) => $q->where('store_id', $store->id))
            ->when($categoryId, function ($q) use ($categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    // Включаем товары из этой категории и всех её дочерних
                    $childIds = $category->children()->pluck('id');
                    $ids = $childIds->push($category->id);
                    $q->whereIn('category_id', $ids);
                } else {
                    $q->where('category_id', $categoryId);
                }
            })
            ->with('store', 'category')
            ->where('is_available', true)
            ->paginate(12);

        $cart = session()->get('cart', []);

        $activeOrders = collect();
        if (Auth::check() && Auth::user()->isCustomer()) {
            $activeOrders = Order::where('customer_id', Auth::id())
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->latest()
                ->get();
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