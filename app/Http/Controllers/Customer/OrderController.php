<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('customer_id', auth()->id())->latest()->paginate(10);
        return view('customer.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) abort(403);
        return view('customer.orders.show', compact('order'));
    }

    public function status(Order $order)
    {
        if ($order->customer_id !== auth()->id()) abort(403);
        return response()->json([
            'status'      => $order->status,
            'statusLabel' => $order->statusLabel(),
        ]);
    }

    public function statusBar(Order $order)
    {
        if ($order->customer_id !== auth()->id()) abort(403);
        return view('customer.orders.partials.status-bar', compact('order'));
    }
}