<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    if (!$order) return false;

    return $user->id === $order->customer_id ||
           ($user->store && $user->store->id === $order->store_id) ||
           $user->id === $order->courier_id;
});