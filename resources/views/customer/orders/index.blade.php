<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Мои заказы
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @forelse ($orders as $order)
                @php
                    $isActive = !in_array($order->status, ['delivered', 'cancelled']);
                    $isDelivered = $order->status === 'delivered';
                    $borderColor = $isActive ? 'border-l-4 border-green-500 bg-green-50' : ($isDelivered ? 'border-l-4 border-blue-500 bg-blue-50' : 'border-l-4 border-gray-300 bg-gray-50');
                @endphp
                @if ($order->status !== 'cancelled')
                <div class="bg-white shadow rounded-lg p-4 mb-2 flex justify-between items-center {{ $borderColor }}">
                    <div>
                        <p class="font-semibold">Заказ #{{ $order->id }} от {{ $order->created_at->format('d.m.Y') }}</p>
                        <p class="text-sm text-gray-600">
                            {{ $order->statusLabel() }} — {{ $order->total_price }} руб.
                        </p>
                        <p class="text-xs text-gray-500">{{ $order->store->name }}</p>
                    </div>
                    <a href="{{ route('customer.orders.show', $order) }}" class="text-blue-500 hover:underline">Подробнее</a>
                </div>
                @endif
            @empty
                <p class="text-gray-600">У вас пока нет заказов.</p>
            @endforelse

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>