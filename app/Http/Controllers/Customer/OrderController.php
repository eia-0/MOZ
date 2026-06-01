<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * История заказов покупателя.
     */
    public function index()
    {
        $orders = Order::where('customer_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Детали заказа (с картой и статусом).
     */
    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }
        return view('customer.orders.show', compact('order'));
    }
}