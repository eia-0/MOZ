<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
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
        $ordersCount = $store->orders()->count();

        return view('store.dashboard', compact('store', 'productsCount', 'ordersCount'));
    }
}