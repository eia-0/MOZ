<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Корзина</h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (empty($cart))
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg">Ваша корзина пуста.</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block text-blue-600 hover:underline">Перейти в каталог</a>
                </div>
            @else
                @php
                    $cartTotal = 0;
                    $store = null;
                    foreach ($cart as $productId => $quantity) {
                        $product = $products[$productId] ?? null;
                        if ($product) {
                            $cartTotal += $product->price * $quantity;
                            $store = $product->store ?? null;
                        }
                    }
                    $minOrder = $store->min_order ?? 0;
                    $freeFrom = $store->free_delivery_from ?? null;
                @endphp

                @if ($cartTotal < $minOrder && $store)
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl mb-4 text-sm">
                        Минимальная сумма заказа в «{{ $store->name }}»: {{ $minOrder }} ₽. Сейчас: {{ $cartTotal }} ₽.
                    </div>
                @endif
                @if ($freeFrom && $cartTotal < $freeFrom && $store)
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl mb-4 text-sm">
                        Бесплатная доставка при заказе от {{ $freeFrom }} ₽. Сейчас: {{ $cartTotal }} ₽.
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-4 text-left text-sm font-medium text-gray-600">Товар</th>
                                <th class="p-4 text-left text-sm font-medium text-gray-600 hidden sm:table-cell">Цена</th>
                                <th class="p-4 text-left text-sm font-medium text-gray-600">Кол-во</th>
                                <th class="p-4 text-left text-sm font-medium text-gray-600 hidden sm:table-cell">Сумма</th>
                                <th class="p-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cart as $productId => $quantity)
                                @php $product = $products[$productId] ?? null @endphp
                                @if ($product)
                                    <tr class="border-t">
                                        <td class="p-4">
                                            <div class="flex items-center gap-3">
                                                @if ($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                         class="w-12 h-12 rounded-lg object-cover hidden sm:block">
                                                @endif
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                                    <p class="text-sm text-gray-500 sm:hidden">{{ $product->price }} ₽</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4 hidden sm:table-cell">{{ $product->price }} ₽</td>
                                        <td class="p-4">
                                            <form action="{{ route('cart.update', $productId) }}" method="POST" class="flex items-center">
                                                @csrf @method('PATCH')
                                                <input type="number" name="quantity" value="{{ $quantity }}" min="0"
                                                       class="w-14 border rounded-lg text-center text-sm py-1"
                                                       onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td class="p-4 hidden sm:table-cell font-medium">{{ $product->price * $quantity }} ₽</td>
                                        <td class="p-4">
                                            <form action="{{ route('cart.remove', $productId) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button class="text-gray-400 hover:text-red-500 text-xl leading-none">&times;</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row justify-between items-end gap-4">
                    <div class="text-right">
                        <p class="text-lg text-gray-700">Итого: <span class="font-bold text-2xl text-green-600">{{ $cartTotal }} ₽</span></p>
                        @if ($store && $store->delivery_fee > 0 && !($freeFrom && $cartTotal >= $freeFrom))
                            <p class="text-sm text-gray-500">+ доставка {{ $store->delivery_fee }} ₽</p>
                        @endif
                    </div>
                    <a href="{{ route('checkout') }}" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-8 rounded-xl transition shadow-md hover:shadow-lg">
                        Оформить заказ
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>