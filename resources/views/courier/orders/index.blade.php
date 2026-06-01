<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Заказы на доставку
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

            <h3 class="text-lg font-semibold mb-3">Активные</h3>
            @forelse ($activeOrders as $order)
                @php
                    $isFree = ($order->status === 'waiting_courier' && is_null($order->courier_id));
                    $isMine = ($order->courier_id === auth()->id());
                    $cardColor = $isFree ? 'border-l-4 border-green-500 bg-green-50' : ($isMine ? 'border-l-4 border-yellow-500 bg-yellow-50' : '');
                @endphp
                <div class="bg-white shadow rounded-lg p-4 mb-2 {{ $cardColor }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold">Заказ #{{ $order->id }}</p>
                            <p class="text-sm text-gray-600">
                                <strong>Адрес доставки:</strong> {{ $order->delivery_address }}<br>
                                <strong>Тел.:</strong> {{ $order->customer->phone ?? '—' }}
                            </p>
                            <div class="mt-1 text-sm text-gray-600">
                                <span class="font-medium">Ресторан: {{ $order->store->name }}</span>
                                @if ($order->store->phone)
                                    , 📞 {{ $order->store->phone }}
                                @endif
                                @if ($order->store->address)
                                    , {{ $order->store->address }}
                                @endif
                            </div>
                            <p class="text-sm mt-1">
                                Статус: {{ $order->statusLabel() }}
                                @if ($isFree)
                                    <span class="text-green-600 font-bold">(свободный)</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <a href="{{ route('courier.orders.show', $order) }}" class="text-blue-500 hover:underline text-sm" title="Карта">
                                📍 Карта
                            </a>
                            @if ($isFree)
                                <form action="{{ route('courier.orders.accept', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm"
                                            onclick="this.disabled=true; this.form.submit();">
                                        Принять заказ
                                    </button>
                                </form>
                            @elseif ($isMine)
                                @if ($order->status === 'courier_assigned')
                                    <form action="{{ route('courier.orders.update-status', $order) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="in_transit">
                                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm"
                                                onclick="this.disabled=true; this.form.submit();">
                                            🚀 Я забрал
                                        </button>
                                    </form>
                                @elseif ($order->status === 'in_transit')
                                    <form action="{{ route('courier.orders.update-status', $order) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm"
                                                onclick="this.disabled=true; this.form.submit();">
                                            ✅ Доставил
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 mb-6">Нет активных заказов.</p>
            @endforelse

            @if ($completedOrders->isNotEmpty())
                <h3 class="text-lg font-semibold mt-8 mb-3">Завершённые</h3>
                @foreach ($completedOrders as $order)
                    <div class="bg-white shadow rounded-lg p-4 mb-2 flex justify-between items-center border-l-4 border-red-500 bg-red-50">
                        <div>
                            <p class="font-semibold">Заказ #{{ $order->id }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $order->delivery_address }}<br>
                                <strong>Тел.:</strong> {{ $order->customer->phone ?? '—' }}
                            </p>
                            <p class="text-sm">Статус: {{ $order->statusLabel() }}</p>
                        </div>
                        <div>
                            <a href="{{ route('courier.orders.show', $order) }}" class="text-blue-500 hover:underline text-sm">📍 Карта</a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>