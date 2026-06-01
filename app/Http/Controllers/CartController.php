<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Показать корзину.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return view('cart.index', compact('cart', 'products'));
    }

    /**
     * Добавить товар в корзину.
     */
    public function add(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + 1;
        session()->put('cart', $cart);

        return back()->with('success', 'Товар добавлен в корзину');
    }

    /**
     * Обновить количество (или удалить, если 0).
     */
    public function update(Request $request, $productId)
    {
        $cart = session()->get('cart', []);
        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < 1) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }
        session()->put('cart', $cart);

        return back();
    }

    /**
     * Удалить товар из корзины.
     */
    public function remove($productId)
    {
        $cart = session()->get('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);

        return back();
    }
}