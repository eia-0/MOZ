<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $store = Auth::user()->store;
        if (!$store) {
            return redirect()->route('home')->with('error', 'У вас нет магазина');
        }

        $productsCount = $store->products()->count();
        $orders = $store->orders();

        $newCount          = (clone $orders)->where('status', 'new')->count();
        $processingCount   = (clone $orders)->whereIn('status', ['accepted', 'preparing', 'ready'])->count();
        $deliveryCount     = (clone $orders)->whereIn('status', ['waiting_courier', 'courier_assigned', 'in_transit'])->count();
        $deliveredCount    = (clone $orders)->where('status', 'delivered')->count();
        $ordersCount       = $orders->count();

        return view('store.dashboard', compact(
            'store',
            'productsCount',
            'ordersCount',
            'newCount',
            'processingCount',
            'deliveryCount',
            'deliveredCount'
        ));
    }
}