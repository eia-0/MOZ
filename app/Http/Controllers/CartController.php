<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        return view('cart.index', compact('cart', 'products'));
    }

    public function add(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);
        $currentQty = $cart[$product->id] ?? 0;

        // Проверка остатка
        if (!is_null($product->stock)) {
            if (($currentQty + 1) > $product->stock) {
                return back()->with('error', 'Недостаточно товара на складе. Доступно: ' . $product->stock . ' шт.');
            }
        }

        $cart[$product->id] = $currentQty + 1;
        session()->put('cart', $cart);
        return back()->with('success', 'Товар добавлен в корзину');
    }

    public function update(Request $request, $productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return back()->with('error', 'Товар не найден');
        }
        $cart = session()->get('cart', []);
        $quantity = (int) $request->input('quantity', 1);

        if ($quantity < 1) {
            unset($cart[$productId]);
        } else {
            if (!is_null($product->stock) && $quantity > $product->stock) {
                return back()->with('error', 'Недостаточно товара на складе. Максимум: ' . $product->stock . ' шт.');
            }
            $cart[$productId] = $quantity;
        }
        session()->put('cart', $cart);
        return back();
    }

    public function remove($productId)
    {
        $cart = session()->get('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);
        return back();
    }
}