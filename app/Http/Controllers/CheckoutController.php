<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->with('store')->get()->keyBy('id');
        $store = $products->first()->store ?? null;
        if (!$store) {
            return back()->with('error', 'Некорректные товары в корзине');
        }

        $total = 0;
        foreach ($cart as $productId => $qty) {
            if (isset($products[$productId])) {
                $total += $products[$productId]->price * $qty;
            }
        }

        $deliveryFee = $store->delivery_fee ?? 0;
        if ($store->free_delivery_from && $total >= $store->free_delivery_from) {
            $deliveryFee = 0;
        }

        $addresses = Auth::check() ? Auth::user()->addresses()->latest()->get() : collect();
        $lastAddress = $addresses->first();

        return view('checkout.index', compact('cart', 'products', 'store', 'total', 'deliveryFee', 'addresses', 'lastAddress'));
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        $validated = $request->validate([
            'store_id'           => 'required|exists:stores,id',
            'delivery_type'      => 'required|in:pickup,delivery',
            'address_id'         => 'nullable|exists:user_addresses,id',
            'street'             => 'required_if:delivery_type,delivery|nullable|string|max:255',
            'house'              => 'required_if:delivery_type,delivery|nullable|string|max:50',
            'floor'              => 'nullable|integer|min:1|max:100',
            'apartment'          => 'nullable|string|max:10',
            'entrance'           => 'nullable|string|max:10',
            'phone'              => 'required_if:delivery_type,delivery|nullable|string|max:20',
            'delivery_latitude'  => 'required_if:delivery_type,delivery|nullable|numeric',
            'delivery_longitude' => 'required_if:delivery_type,delivery|nullable|numeric',
            'leave_at_door'      => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)
                ->where('store_id', $validated['store_id'])
                ->get()
                ->keyBy('id');

            if ($products->count() != count($productIds)) {
                throw new \Exception('Некоторые товары не принадлежат выбранному магазину');
            }

            $store = Store::findOrFail($validated['store_id']);
            $total = 0;
            foreach ($cart as $productId => $qty) {
                $total += $products[$productId]->price * $qty;
            }

            if ($total < $store->min_order) {
                throw new \Exception('Минимальная сумма заказа: ' . $store->min_order . ' руб. Сейчас в корзине: ' . $total . ' руб.');
            }

            // Адрес доставки
            $deliveryAddress = null;
            $deliveryLat = null;
            $deliveryLng = null;
            if ($validated['delivery_type'] === 'delivery') {
                $parts = [];
                if (!empty($validated['street'])) $parts[] = 'ул. ' . $validated['street'];
                if (!empty($validated['house'])) $parts[] = 'д. ' . $validated['house'];
                if (!empty($validated['floor'])) $parts[] = 'этаж ' . $validated['floor'];
                if (!empty($validated['apartment'])) $parts[] = 'кв. ' . $validated['apartment'];
                if (!empty($validated['entrance'])) $parts[] = 'подъезд ' . $validated['entrance'];
                $deliveryAddress = implode(', ', $parts);

                if (!empty($validated['phone'])) {
                    Auth::user()->update(['phone' => $validated['phone']]);
                }
                $deliveryLat = $validated['delivery_latitude'];
                $deliveryLng = $validated['delivery_longitude'];

                // Сохраняем адрес только если не выбран существующий
                if (empty($validated['address_id'])) {
                    UserAddress::create([
                        'user_id'   => Auth::id(),
                        'street'    => $validated['street'] ?? '',
                        'house'     => $validated['house'] ?? '',
                        'floor'     => $validated['floor'] ?? null,
                        'apartment' => $validated['apartment'] ?? '',
                        'entrance'  => $validated['entrance'] ?? '',
                        'latitude'  => $deliveryLat,
                        'longitude' => $deliveryLng,
                    ]);
                }
            }

            $deliveryInstructions = null;
            if ($validated['delivery_type'] === 'delivery') {
                $deliveryInstructions = !empty($validated['leave_at_door']) ? 'Оставить у двери' : 'Передать в руки';
            }

            $deliveryFee = ($validated['delivery_type'] === 'delivery') ? $store->delivery_fee : 0;
            if ($validated['delivery_type'] === 'delivery' && $store->free_delivery_from && $total >= $store->free_delivery_from) {
                $deliveryFee = 0;
            }

            $totalPrice = $total + $deliveryFee;

            $order = Order::create([
                'customer_id'           => Auth::id(),
                'store_id'              => $store->id,
                'delivery_type'         => $validated['delivery_type'],
                'delivery_address'      => $deliveryAddress,
                'delivery_latitude'     => $deliveryLat,
                'delivery_longitude'    => $deliveryLng,
                'delivery_instructions' => $deliveryInstructions,
                'status'                => 'new',
                'total_price'           => $totalPrice,
                'delivery_fee'          => $deliveryFee,
            ]);

            foreach ($cart as $productId => $qty) {
                $product = $products[$productId];
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'price'      => $product->price,
                ]);

                // Списание со склада
                if (!is_null($product->stock)) {
                    $newStock = max(0, $product->stock - $qty);
                    $product->update(['stock' => $newStock]);
                    $product->stockMovements()->create([
                        'type'     => 'out',
                        'quantity' => $qty,
                        'comment'  => 'Продажа по заказу #' . $order->id,
                        'order_id' => $order->id,
                    ]);
                }
            }

            DB::commit();
            session()->forget('cart');

            // ★ ВОТ ЗДЕСЬ ГЛАВНОЕ – редирект в каталог
            return redirect()->route('home')
                ->with('success', 'Заказ оформлен! Ожидайте подтверждения магазина.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка оформления заказа: ' . $e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}