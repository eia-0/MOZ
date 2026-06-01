<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index()
    {
        $store = Auth::user()->store;
        if (!$store) {
            return redirect()->route('home')->with('error', 'У вас нет магазина');
        }
        $products = $store->products()->with('category')->latest()->paginate(20);
        return view('store.stock.index', compact('products'));
    }

    public function update(Request $request, Product $product)
    {
        $store = Auth::user()->store;
        if (!$store || $product->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'action'   => 'required|in:add,subtract',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validated['action'] === 'subtract') {
            $newStock = $product->stock - $validated['quantity'];
            if ($newStock < 0) {
                return back()->with('error', 'Недостаточно товара на складе для списания');
            }
            $product->stock = $newStock;
        } else {
            $product->stock += $validated['quantity'];
        }

        $product->save();

        return back()->with('success', 'Остаток обновлён. Текущий остаток: ' . $product->stock);
    }
}