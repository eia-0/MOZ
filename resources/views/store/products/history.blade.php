<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            История товара: {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('store.products.index') }}" class="text-blue-500 mb-4 inline-block">← К списку товаров</a>

            <table class="w-full bg-white shadow rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left">Дата</th>
                        <th class="p-4 text-left">Тип</th>
                        <th class="p-4 text-left">Кол-во</th>
                        <th class="p-4 text-left">Комментарий</th>
                        <th class="p-4 text-left">Заказ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $move)
                        <tr class="border-t">
                            <td class="p-4">{{ \Carbon\Carbon::parse($move->created_at)->setTimezone('Asia/Yekaterinburg')->format('d.m.Y H:i') }}</td>
                            <td class="p-4">
                                @if ($move->type === 'in') <span class="text-green-600">Поступление</span>
                                @elseif ($move->type === 'out') <span class="text-red-600">Продажа</span>
                                @else <span class="text-gray-600">Корректировка</span>
                                @endif
                            </td>
                            <td class="p-4">{{ $move->quantity }}</td>
                            <td class="p-4">{{ $move->comment ?? '—' }}</td>
                            <td class="p-4">
                                @if ($move->order_id)
                                    <a href="{{ route('store.orders.show', $move->order_id) }}" class="text-blue-500">#{{ $move->order_id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Нет записей о движении товара.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</x-app-layout>