<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Главное: {{ $store->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Карточки статистики --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white shadow rounded-lg p-6 border-l-4 border-green-500">
                    <h3 class="text-lg font-semibold text-gray-700">Новые</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $newCount }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-500">
                    <h3 class="text-lg font-semibold text-gray-700">В обработке</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $processingCount }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-6 border-l-4 border-orange-500">
                    <h3 class="text-lg font-semibold text-gray-700">В доставке</h3>
                    <p class="text-3xl font-bold text-orange-600">{{ $deliveryCount }}</p>
                </div>
                <div class="bg-white shadow rounded-lg p-6 border-l-4 border-purple-500">
                    <h3 class="text-lg font-semibold text-gray-700">Доставлено</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $deliveredCount }}</p>
                </div>
            </div>

            {{-- Дополнительная информация --}}
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <p class="text-gray-700">Товаров в меню: <strong>{{ $productsCount }}</strong></p>
                <p class="text-gray-700">Всего заказов: <strong>{{ $ordersCount }}</strong></p>
                <p class="text-gray-700">Минимальный заказ: <strong>{{ $store->min_order }} руб.</strong></p>
                <p class="text-gray-700">Доставка: <strong>{{ $store->delivery_fee }} руб.</strong></p>
                @if($store->free_delivery_from)
                    <p class="text-gray-700 mt-2">Бесплатная доставка от <strong>{{ $store->free_delivery_from }} руб.</strong></p>
                @endif
            </div>

            {{-- Быстрые ссылки --}}
            <div class="flex gap-2">
                <a href="{{ route('store.products.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Управление товарами</a>
                <a href="{{ route('store.orders') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Все заказы</a>
                <a href="{{ route('store.settings') }}" class="bg-green-500 text-white px-4 py-2 rounded">Настройки</a>
            </div>
        </div>
    </div>
</x-app-layout>