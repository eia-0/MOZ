<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $activeOrders = Order::where('delivery_type', 'delivery')
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->where(function ($query) {
                $query->whereNull('courier_id')
                      ->orWhere('courier_id', auth()->id());
            })->latest()->get();

        $completedOrders = Order::where('delivery_type', 'delivery')
            ->where('status', 'delivered')
            ->where('courier_id', auth()->id())
            ->latest()->get();

        return view('courier.orders.index', compact('activeOrders', 'completedOrders'));
    }

    public function show(Order $order)
    {
        if (
            ($order->courier_id === null && $order->delivery_type === 'delivery' && in_array($order->status, ['new','accepted','preparing','ready','waiting_courier'])) ||
            $order->courier_id === auth()->id()
        ) {
            return view('courier.orders.show', compact('order'));
        }
        abort(403);
    }

    public function status(Order $order)
    {
        // Разрешаем смотреть статус, если заказ свободен или принадлежит курьеру
        if (
            ($order->courier_id === null && $order->delivery_type === 'delivery') ||
            $order->courier_id === auth()->id()
        ) {
            return response()->json([
                'status'      => $order->status,
                'statusLabel' => $order->statusLabel(),
            ]);
        }
        abort(403);
    }

    public function accept(Order $order)
    {
        if ($order->status !== 'waiting_courier' || !is_null($order->courier_id)) {
            return back()->with('error', 'Этот заказ уже недоступен.');
        }
        $order->update([
            'status'     => 'courier_assigned',
            'courier_id' => auth()->id(),
        ]);
        return redirect()->route('courier.orders.show', $order)->with('success', 'Вы приняли заказ.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->courier_id !== auth()->id()) abort(403);
        $validTransitions = [
            'courier_assigned' => ['in_transit'],
            'in_transit'       => ['delivered'],
        ];
        $newStatus = $request->input('status');
        if (!isset($validTransitions[$order->status]) || !in_array($newStatus, $validTransitions[$order->status])) {
            return back()->with('error', 'Неверный переход статуса');
        }
        $order->update(['status' => $newStatus]);
        return back()->with('success', 'Статус обновлён');
    }
}