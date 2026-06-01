<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Каталог товаров (главная).
     */
    public function index(Request $request)
    {
        $storeId = $request->query('store_id');

        // Если передан store_id, берём конкретный магазин, иначе первый попавшийся
        $store = $storeId ? Store::find($storeId) : Store::first();

        $products = Product::when($store, fn($q) => $q->where('store_id', $store->id))
            ->with('store', 'category')
            ->where('is_available', true)
            ->paginate(12);

        $cart = session()->get('cart', []);

        // Активные заказы покупателя
        $activeOrders = collect();
        if (Auth::check() && Auth::user()->isCustomer()) {
            $activeOrders = Order::where('customer_id', Auth::id())
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->latest()
                ->get();
        }

        // Статус открыт/закрыт
        $isOpen = $store ? $store->isOpenNow() : false;

        return view('products.index', compact('products', 'cart', 'activeOrders', 'store', 'isOpen'));
    }

    /**
     * Детальная страница товара.
     */
    public function show(Product $product)
    {
        $product->load('store');
        $cart = session()->get('cart', []);
        return view('products.show', compact('product', 'cart'));
    }
}