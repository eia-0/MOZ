<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private function getStoreOrRedirect()
    {
        $store = Auth::user()->store;
        if (!$store) {
            redirect()->route('home')->with('error', 'У вас нет магазина.')->send();
            exit;
        }
        return $store;
    }

    private function ensureStoreOwnership(Order $order): void
    {
        $store = $this->getStoreOrRedirect();
        if ($order->store_id !== $store->id) {
            abort(403, 'Нет доступа к этому заказу');
        }
    }

    public function index()
    {
        $store = $this->getStoreOrRedirect();
        $orders = $store->orders()->with('customer')->latest()->paginate(10);
        return view('store.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->ensureStoreOwnership($order);
        $order->load('items.product', 'courier', 'customer', 'store');
        return view('store.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->ensureStoreOwnership($order);

        $validTransitions = [
            'new'       => ['accepted', 'cancelled'],
            'accepted'  => ['preparing'],
            'preparing' => ['ready'],
            'ready'     => $order->delivery_type === 'pickup'
                            ? ['delivered']
                            : ['waiting_courier'],
        ];

        $newStatus = $request->input('status');
        if (!isset($validTransitions[$order->status]) || !in_array($newStatus, $validTransitions[$order->status])) {
            return back()->with('error', 'Неверный переход статуса');
        }

        $order->update(['status' => $newStatus]);
        broadcast(new OrderStatusChanged($order))->toOthers();

        return redirect()->route('store.orders.show', $order)
    ->with('success', "Статус изменён на \"$newStatus\"");
    }

    public function destroy(Order $order)
    {
        $this->ensureStoreOwnership($order);

        \DB::beginTransaction();
        try {
            $order->items()->delete();
            $order->delete();
            \DB::commit();
            return redirect()->route('store.orders')->with('success', 'Заказ удалён.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Не удалось удалить заказ.');
        }
    }
}