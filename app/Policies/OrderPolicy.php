<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Просмотр заказа.
     */
    public function view(User $user, Order $order): bool
    {
        return match ($user->role) {
            'customer' => $user->id === $order->customer_id,
            'store'    => $user->store && $user->store->id === $order->store_id,
            'courier'  => $user->id === $order->courier_id,
            default    => false,
        };
    }

    /**
     * Изменение статуса заказа.
     */
    public function update(User $user, Order $order): bool
    {
        return match ($user->role) {
            'store'   => $user->store && $user->store->id === $order->store_id,
            'courier' => $user->id === $order->courier_id,
            default   => false,
        };
    }
}