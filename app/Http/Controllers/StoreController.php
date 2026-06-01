<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Показать страницу одного магазина с товарами.
     */
    public function show(Store $store)
    {
        $products = $store->products()
            ->where('is_available', true)
            ->with('category')
            ->paginate(12);

        $cart = session()->get('cart', []);

        return view('store.show', compact('store', 'products', 'cart'));
    }
}