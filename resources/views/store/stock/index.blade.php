<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Управление складом
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
            @endif

            <table class="w-full bg-white shadow rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left">Товар</th>
                        <th class="p-4 text-left">Категория</th>
                        <th class="p-4 text-left">Остаток</th>
                        <th class="p-4 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-t">
                            <td class="p-4">{{ $product->name }}</td>
                            <td class="p-4">{{ $product->category->name ?? '—' }}</td>
                            <td class="p-4 font-bold {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $product->stock }}</td>
                            <td class="p-4">
                                {{-- Списание --}}
                                <form action="{{ route('store.stock.update', $product) }}" method="POST" class="flex items-center gap-2 mb-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="subtract">
                                    <input type="number" name="quantity" placeholder="Кол-во" class="w-20 border rounded p-1" min="1" required>
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-sm">Списать</button>
                                </form>
                                {{-- Добавление --}}
                                <form action="{{ route('store.stock.update', $product) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="add">
                                    <input type="number" name="quantity" placeholder="Кол-во" class="w-20 border rounded p-1" min="1" required>
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded text-sm">Добавить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">Нет товаров</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>