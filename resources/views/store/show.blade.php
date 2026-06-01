<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Каталог</a>
            <span>/</span>
            <a href="{{ route('store.show', $product->store) }}" class="hover:text-blue-600">{{ $product->store->name }}</a>
            <span>/</span>
            <span class="text-gray-900">{{ $product->name }}</span>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="aspect-square bg-gray-100">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-6 sm:p-8 flex flex-col">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                        @if($product->weight)
                            <p class="text-sm text-gray-500 mb-4">{{ $product->weight }} г</p>
                        @endif
                        <p class="text-gray-700 mb-6 leading-relaxed">{{ $product->description ?? 'Описание отсутствует' }}</p>

                        <div class="mt-auto space-y-4">
                            <div class="flex items-baseline justify-between">
                                <span class="text-3xl font-bold text-green-600">{{ $product->price }} ₽</span>
                                @if (!is_null($product->stock))
                                    <span class="text-sm text-gray-600">
                                        В наличии: {{ $product->stock > 0 ? $product->stock . ' шт.' : 'закончился' }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">В наличии: много</span>
                                @endif
                            </div>

                            @php $inCart = isset($cart[$product->id]); $qty = $cart[$product->id] ?? 0; @endphp
                            @if ($inCart)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <form action="{{ route('cart.update', $product->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="quantity" value="{{ $qty - 1 }}">
                                            <button class="w-10 h-10 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 text-xl transition">
                                                −
                                            </button>
                                        </form>
                                        <span class="text-xl font-bold">{{ $qty }}</span>
                                        <form action="{{ route('cart.add', $product) }}" method="POST">
                                            @csrf
                                            <button class="w-10 h-10 flex items-center justify-center rounded-full bg-green-100 text-green-600 hover:bg-green-200 text-xl transition">
                                                +
                                            </button>
                                        </form>
                                    </div>
                                    <form action="{{ route('cart.remove', $product->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-400 hover:text-red-500 text-2xl leading-none">&times;</button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('cart.add', $product) }}" method="POST">
                                    @csrf
                                    <button class="w-full py-3 px-6 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition shadow-md hover:shadow-lg">
                                        В корзину
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>