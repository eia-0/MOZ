<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function edit()
    {
        $store = Auth::user()->store;
        if (!$store) {
            return redirect()->route('home')->with('error', 'У вас нет магазина');
        }
        return view('store.settings', compact('store'));
    }

    public function update(Request $request)
    {
        $store = Auth::user()->store;
        if (!$store) {
            return redirect()->route('home')->with('error', 'У вас нет магазина');
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'latitude'            => 'required|numeric',
            'longitude'           => 'required|numeric',
            'delivery_fee'        => 'required|numeric|min:0',
            'min_order'           => 'required|numeric|min:0',
            'free_delivery_from'  => 'nullable|numeric|min:0',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',   // новый адрес
            'working_hours'       => 'nullable|array',
            'working_hours.*.open'  => 'nullable|date_format:H:i',
            'working_hours.*.close' => 'nullable|date_format:H:i',
        ]);

        $store->update($validated);

        return redirect()->route('store.settings')->with('success', 'Настройки сохранены');
    }
}