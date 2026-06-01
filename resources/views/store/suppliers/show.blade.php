<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Поставщик: {{ $supplier->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('store.suppliers.index') }}" class="text-blue-500 mb-4 inline-block">← Все поставщики</a>

            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <p><strong>Телефон:</strong> {{ $supplier->phone ?? '—' }}</p>
                <p><strong>Описание:</strong> {{ $supplier->description ?? '—' }}</p>
            </div>

            {{-- Итоговая сумма --}}
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-lg mb-2">Финансы</h3>
                <p>Продано единиц: <strong>{{ $totalSoldQuantity }}</strong></p>
                <p>К оплате поставщику: <strong>{{ number_format($totalDebt, 2) }} ₽</strong></p>
            </div>

            {{-- Товары по категориям --}}
            <h3 class="font-semibold text-lg mb-4">Товары поставщика</h3>
            @forelse ($grouped as $parentName => $items)
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-2">{{ $parentName }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($items as $product)
                            <div class="bg-white shadow rounded-lg p-4">
                                <h5 class="font-semibold">{{ $product->name }}</h5>
                                <p class="text-sm text-gray-500">
                                    Категория: {{ $product->category->name ?? '—' }}
                                </p>
                                <p class="text-sm">Цена продажи: {{ $product->price }} ₽</p>
                                <p class="text-sm">Цена поставщика: {{ $product->supplier_price ?? '—' }} ₽</p>
                                <p class="text-sm">Остаток: {{ $product->stock ?? '∞' }}</p>
                                <p class="text-sm">Продано: {{ $product->sold_quantity }} шт.</p>
                                <p class="text-sm font-semibold text-green-600">
                                    Долг поставщику: {{ number_format($product->debt, 2) }} ₽
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500">У этого поставщика нет товаров.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>