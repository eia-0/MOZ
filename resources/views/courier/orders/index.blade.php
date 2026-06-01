<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Заказы на доставку</h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('error') }}</div>
            @endif

            <h3 class="text-lg font-semibold text-gray-800 mb-3">Активные</h3>
            @forelse ($activeOrders as $order)
                @php
                    $isFree = ($order->status === 'waiting_courier' && is_null($order->courier_id));
                    $isMine = ($order->courier_id === auth()->id());
                    $cardClass = $isFree ? 'border-l-green-500 bg-green-50' : ($isMine ? 'border-l-yellow-500 bg-yellow-50' : '');
                @endphp
                <div class="bg-white rounded-xl shadow-sm border-l-4 {{ $cardClass }} p-4 mb-3">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-800">Заказ #{{ $order->id }}</p>
                                <a href="{{ route('courier.orders.show', $order) }}" class="text-blue-600 hover:underline text-sm ml-4">Подробнее</a>
                            </div>
                            <p class="text-sm text-gray-600">{{ $order->delivery_address }}</p>
                            <p class="text-sm text-gray-700">Статус: {{ $order->statusLabel() }} @if($isFree) <span class="text-green-600 font-medium">(свободный)</span> @endif</p>
                            @if($order->customer->phone)
                                <p class="text-xs text-gray-500">Тел. клиента: {{ $order->customer->phone }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            @if ($isFree)
                                <form action="{{ route('courier.orders.accept', $order) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">Принять</button>
                                </form>
                            @elseif ($isMine)
                                @if ($order->status === 'courier_assigned')
                                    <form action="{{ route('courier.orders.update-status', $order) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="in_transit">
                                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">🚀 Я забрал</button>
                                    </form>
                                @elseif ($order->status === 'in_transit')
                                    <form action="{{ route('courier.orders.update-status', $order) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="delivered">
                                        <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-medium transition">✅ Доставил</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">Нет активных заказов.</p>
            @endforelse

            @if ($completedOrders->isNotEmpty())
                <h3 class="text-lg font-semibold text-gray-800 mt-8 mb-3">Завершённые</h3>
                @foreach ($completedOrders as $order)
                    <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-red-500 bg-red-50 p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-gray-800">Заказ #{{ $order->id }}</p>
                                    <a href="{{ route('courier.orders.show', $order) }}" class="text-blue-600 hover:underline text-sm ml-4">Подробнее</a>
                                </div>
                                <p class="text-sm text-gray-600">{{ $order->delivery_address }}</p>
                                <p class="text-sm text-gray-700">Статус: {{ $order->statusLabel() }}</p>
                                @if($order->customer->phone)
                                    <p class="text-xs text-gray-500">Тел. клиента: {{ $order->customer->phone }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>