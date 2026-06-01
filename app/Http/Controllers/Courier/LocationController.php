<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\CourierLocation;
use App\Models\Order;
use App\Events\CourierLocationUpdated;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Сохранение координат курьера и трансляция.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'   => 'required|exists:orders,id',
            'latitude'   => 'required|numeric',
            'longitude'  => 'required|numeric',
        ]);

        $order = Order::find($validated['order_id']);
        if ($order->courier_id !== auth()->id()) {
            return response()->json(['error' => 'Не ваш заказ'], 403);
        }

        CourierLocation::create([
            'courier_id'  => auth()->id(),
            'order_id'    => $order->id,
            'latitude'    => $validated['latitude'],
            'longitude'   => $validated['longitude'],
            'recorded_at' => now(),
        ]);

        // Трансляция события о местоположении
        broadcast(new CourierLocationUpdated($order, $validated['latitude'], $validated['longitude']));

        return response()->json(['status' => 'ok']);
    }
}