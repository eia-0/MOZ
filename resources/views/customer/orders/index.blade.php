<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Мои заказы</h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('error') }}</div>
            @endif

            @forelse ($orders as $order)
                @php
                    $isActive = !in_array($order->status, ['delivered', 'cancelled']);
                    $isDelivered = $order->status === 'delivered';
                    $borderClass = $isActive ? 'border-l-green-500 bg-green-50' : ($isDelivered ? 'border-l-blue-500 bg-blue-50' : '');
                @endphp
                <a href="{{ route('customer.orders.show', $order) }}" class="block bg-white rounded-xl shadow-sm border-l-4 {{ $borderClass }} p-4 mb-3 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-800">Заказ #{{ $order->id }} от {{ $order->created_at->format('d.m.Y') }}</p>
                            <p class="text-sm text-gray-600">{{ $order->store->name }}</p>
                            <p class="text-sm text-gray-700 mt-1">Статус: <span class="font-medium">{{ $order->statusLabel() }}</span></p>
                        </div>
                        <span class="text-sm font-bold text-green-600">{{ $order->total_price }} ₽</span>
                    </div>
                </a>
            @empty
                <div class="text-center py-12">
                    <p class="text-gray-500">У вас пока нет заказов.</p>
                    <a href="{{ route('home') }}" class="mt-2 inline-block text-blue-600 hover:underline">Перейти в каталог</a>
                </div>
            @endforelse

            <div class="mt-6">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>