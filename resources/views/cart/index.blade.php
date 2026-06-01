<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Корзина
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (empty($cart))
                <p class="text-gray-600">Ваша корзина пуста.</p>
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
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        Минимальная сумма заказа в магазине «{{ $store->name }}»: {{ $minOrder }} руб.
                        Сейчас в корзине: {{ $cartTotal }} руб.
                    </div>
                @endif

                @if ($freeFrom && $cartTotal < $freeFrom && $store)
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                        Бесплатная доставка при заказе от {{ $freeFrom }} руб.
                        Сейчас в корзине: {{ $cartTotal }} руб.
                    </div>
                @endif

                <table class="w-full bg-white shadow rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4 text-left">Товар</th>
                            <th class="p-4 text-left">Цена</th>
                            <th class="p-4 text-left">Кол-во</th>
                            <th class="p-4 text-left">Сумма</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cart as $productId => $quantity)
                            @php $product = $products[$productId] ?? null @endphp
                            @if ($product)
                                <tr class="border-t">
                                    <td class="p-4 flex items-center gap-3">
                                        {{-- Миниатюра фото --}}
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                 class="w-12 h-12 object-cover rounded">
                                        @else
                                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">
                                                Нет фото
                                            </div>
                                        @endif
                                        <span>{{ $product->name }}</span>
                                    </td>
                                    <td class="p-4">{{ $product->price }} руб.</td>
                                    <td class="p-4">
                                        <form action="{{ route('cart.update', $productId) }}" method="POST" class="flex items-center">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantity" value="{{ $quantity }}" min="0"
                                                   class="w-16 border rounded text-center"
                                                   onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="p-4">{{ $product->price * $quantity }} руб.</td>
                                    <td class="p-4">
                                        <form action="{{ route('cart.remove', $productId) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xl leading-none" title="Удалить">
                                                &times;
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 text-right">
                    <a href="{{ route('checkout') }}" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600">
                        Оформить заказ
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>