<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Списание товара: {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('store.products.writeoff', $product) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf

                <div class="mb-4">
                    <p class="text-gray-700">Текущий остаток: <strong>{{ $product->stock ?? 'не ограничен' }}</strong></p>
                    @if (!is_null($product->stock) && $product->stock == 0)
                        <p class="text-red-500">Товар закончился, списание невозможно.</p>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Количество для списания (шт.)</label>
                    <input type="number" name="quantity" min="1" max="{{ $product->stock ?? 999999 }}" class="w-full border rounded p-2" required {{ $product->stock === 0 ? 'disabled' : '' }}>
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Комментарий</label>
                    <input type="text" name="comment" class="w-full border rounded p-2" placeholder="Причина списания">
                </div>

                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600" {{ $product->stock === 0 ? 'disabled' : '' }}>Списать</button>
            </form>
        </div>
    </div>
</x-app-layout>