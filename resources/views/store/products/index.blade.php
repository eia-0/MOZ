<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Мои товары
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('store.products.create') }}" class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block hover:bg-green-600">
                + Добавить товар
            </a>

            <table class="w-full bg-white shadow rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left">Фото</th>
                        <th class="p-4 text-left">Название</th>
                        <th class="p-4 text-left">Цена</th>
                        <th class="p-4 text-left">Вес (г)</th>
                        <th class="p-4 text-left">Категория</th>
                        <th class="p-4 text-left">Доступен</th>
                        <th class="p-4 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-t">
                            <td class="p-4">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">Нет фото</div>
                                @endif
                            </td>
                            <td class="p-4">{{ $product->name }}</td>
                            <td class="p-4">{{ $product->price }} руб.</td>
                            <td class="p-4">{{ $product->weight }} г</td>
                            <td class="p-4">{{ $product->category->name ?? '—' }}</td>
                            <td class="p-4">{{ $product->is_available ? 'Да' : 'Нет' }}</td>
                            <td class="p-4">
                                <a href="{{ route('store.products.edit', $product) }}" class="text-blue-500 hover:underline">Ред.</a>
                                <form action="{{ route('store.products.destroy', $product) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Удалить товар?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Удал.</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">Товаров пока нет. Нажмите «Добавить товар», чтобы создать первый.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>