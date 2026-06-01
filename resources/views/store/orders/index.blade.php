<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Заказы магазина</h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('error') }}</div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full bg-white shadow rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4 text-left">Номер</th>
                            <th class="p-4 text-left">Клиент</th>
                            <th class="p-4 text-left">Сумма</th>
                            <th class="p-4 text-left">Статус</th>
                            <th class="p-4 text-left">Дата</th>
                            <th class="p-4 text-left">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $rowClass = '';
                                if ($order->status === 'new') {
                                    $rowClass = 'bg-green-50 border-l-4 border-green-500';
                                } elseif ($order->status === 'delivered') {
                                    $rowClass = 'bg-blue-50 border-l-4 border-blue-500';
                                } elseif ($order->status === 'cancelled') {
                                    $rowClass = 'bg-gray-100 text-gray-500 border-l-4 border-gray-400';
                                } elseif ($order->status === 'waiting_courier') {
                                    $rowClass = 'bg-orange-50 border-l-4 border-orange-500';
                                } elseif ($order->status === 'in_transit') {
                                    $rowClass = 'bg-red-50 border-l-4 border-red-500';
                                }
                            @endphp
                            <tr class="border-t {{ $rowClass }}">
                                <td class="p-4">#{{ $order->id }}</td>
                                <td class="p-4">{{ $order->customer->name ?? '—' }}</td>
                                <td class="p-4">
                                    {{ $order->total_price }} руб.
                                    @if($order->delivery_fee == 0 && $order->delivery_type === 'delivery')
                                        <span class="text-xs text-green-600">(бесплатно)</span>
                                    @endif
                                </td>
                                <td class="p-4 font-medium">
                                    {{ $order->statusLabel() }}
                                    @if ($order->status === 'new')
                                        <span class="text-green-600 text-xs ml-1">(новый)</span>
                                    @elseif ($order->status === 'waiting_courier')
                                        <span class="text-orange-600 text-xs ml-1">(ожидает)</span>
                                    @elseif ($order->status === 'in_transit')
                                        <span class="text-red-600 text-xs ml-1">(в пути)</span>
                                    @endif
                                </td>
                                <td class="p-4">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td class="p-4">
                                    <a href="{{ route('store.orders.show', $order) }}" class="text-blue-500 hover:underline">Просмотр</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">Нет заказов.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>