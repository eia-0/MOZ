<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Все заказы (активные + завершённые), кроме отменённых.
     */
    public function index()
    {
        // Активные (не доставленные, не отменённые), где курьер либо null, либо текущий
        $activeOrders = Order::where('delivery_type', 'delivery')
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->where(function ($query) {
                $query->whereNull('courier_id')
                      ->orWhere('courier_id', auth()->id());
            })
            ->latest()
            ->get();

        // Завершённые (доставленные), назначенные этому курьеру
        $completedOrders = Order::where('delivery_type', 'delivery')
            ->where('status', 'delivered')
            ->where('courier_id', auth()->id())
            ->latest()
            ->get();

        return view('courier.orders.index', compact('activeOrders', 'completedOrders'));
    }

    /**
     * Детали заказа.
     */
    public function show(Order $order)
    {
        // Разрешаем смотреть: если заказ ещё не назначен (свободен) и это доставка,
        // или если заказ назначен на этого курьера.
        if (
            ($order->courier_id === null && $order->delivery_type === 'delivery' && in_array($order->status, ['new','accepted','preparing','ready','waiting_courier'])) ||
            $order->courier_id === auth()->id()
        ) {
            return view('courier.orders.show', compact('order'));
        }
        abort(403);
    }

    /**
     * Принять свободный заказ.
     */
    public function accept(Order $order)
    {
        if ($order->status !== 'waiting_courier' || !is_null($order->courier_id)) {
            return back()->with('error', 'Этот заказ уже недоступен.');
        }

        $order->update([
            'status'     => 'courier_assigned',
            'courier_id' => auth()->id(),
        ]);

        broadcast(new OrderStatusChanged($order))->toOthers();

        return redirect()->route('courier.orders')
    ->with('success', 'Вы приняли заказ.');
    }

    /**
     * Смена статуса курьером (забрал / доставил).
     */
    public function updateStatus(Request $request, Order $order)
    {
        if ($order->courier_id !== auth()->id()) {
            abort(403);
        }

        $validTransitions = [
            'courier_assigned' => ['in_transit'],
            'in_transit'       => ['delivered'],
        ];

        $newStatus = $request->input('status');
        if (!isset($validTransitions[$order->status]) || !in_array($newStatus, $validTransitions[$order->status])) {
            return back()->with('error', 'Неверный переход статуса');
        }

        $order->update(['status' => $newStatus]);
        broadcast(new OrderStatusChanged($order))->toOthers();

        return redirect()->route('courier.orders')
    ->with('success', 'Статус обновлён');
    }
}