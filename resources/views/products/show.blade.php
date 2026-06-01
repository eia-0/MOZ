<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6 flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-1/2">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full rounded">
                    @else
                        <div class="w-full h-64 bg-gray-200 flex items-center justify-center text-gray-500">Нет фото</div>
                    @endif
                </div>
                <div class="w-full md:w-1/2">
                    <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
                    <p class="text-gray-600 mt-2">{{ $product->description }}</p>
                    <p class="text-sm text-gray-500 mt-2">Вес: {{ $product->weight }} г</p>
                    <p class="text-3xl font-bold text-green-600 mt-4">{{ $product->price }} руб.</p>

                    @php $inCart = isset($cart[$product->id]); $qty = $cart[$product->id] ?? 0; @endphp
                    @if ($inCart)
                        <div class="flex items-center mt-4 gap-2">
                            <form action="{{ route('cart.update', $product->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $qty - 1 }}">
                                <button class="bg-red-500 text-white w-10 h-10 rounded text-xl">−</button>
                            </form>
                            <span class="text-xl font-bold mx-3">{{ $qty }}</span>
                            <form action="{{ route('cart.add', $product) }}" method="POST">
                                @csrf
                                <button class="bg-green-500 text-white w-10 h-10 rounded text-xl">+</button>
                            </form>
                            <form action="{{ route('cart.remove', $product->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="text-gray-400 hover:text-red-500 text-2xl ml-1">×</button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-6">
                            @csrf
                            <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600 text-lg">В корзину</button>
                        </form>
                    @endif

                    <a href="{{ route('store.show', $product->store) }}" class="text-blue-500 mt-4 inline-block">
                        ← Вернуться в магазин «{{ $product->store->name }}»
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>