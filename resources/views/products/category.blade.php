<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $category->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Информационная панель магазина --}}
            @if ($store)
                <div class="bg-white shadow rounded-lg px-5 py-4 mb-8 flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                        <span class="font-semibold text-base">{{ $store->name }}</span>
                        @if ($store->phone)
                            <span class="text-gray-600">📞 {{ $store->phone }}</span>
                        @endif
                        @if ($isOpen)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                🟢 Открыто
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                🔴 Закрыто
                            </span>
                        @endif
                        @if ($store->working_hours)
                            <span class="text-gray-500 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $store->working_hours_summary }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Кнопка "Назад в каталог" --}}
            <a href="{{ route('home') }}" class="text-blue-500 hover:underline mb-4 inline-block">&larr; Назад в каталог</a>

            {{-- Подкатегории и их товары --}}
            @forelse ($groupedProducts as $subCategoryName => $items)
                <div class="mb-10">
                    <h3 class="text-xl font-bold mb-4">{{ $subCategoryName }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach ($items as $product)
                            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden flex flex-col">
                                <a href="{{ route('product.show', $product) }}" class="block h-48 overflow-hidden">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </a>

                                <div class="p-4 flex flex-col flex-grow">
                                    <h3 class="font-semibold text-lg mb-1 line-clamp-2">
                                        <a href="{{ route('product.show', $product) }}" class="hover:text-blue-600 transition-colors">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    @if($product->weight)
                                        <p class="text-sm text-gray-500">{{ $product->weight }} г</p>
                                    @endif
                                    <p class="mt-2 text-2xl font-bold text-green-600">{{ $product->price }} ₽</p>

                                    <div class="mt-auto pt-3">
                                        @php $inCart = isset($cart[$product->id]); $qty = $cart[$product->id] ?? 0; @endphp
                                        @if ($inCart)
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <form action="{{ route('cart.update', $product->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="quantity" value="{{ $qty - 1 }}">
                                                        <button type="submit"
                                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors">
                                                            −
                                                        </button>
                                                    </form>
                                                    <span class="font-semibold text-lg w-6 text-center">{{ $qty }}</span>
                                                    <form action="{{ route('cart.add', $product) }}" method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition-colors">
                                                            +
                                                        </button>
                                                    </form>
                                                </div>
                                                <form action="{{ route('cart.remove', $product->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors text-2xl leading-none" title="Удалить">
                                                        &times;
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <form action="{{ route('cart.add', $product) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition-colors shadow-sm hover:shadow-md">
                                                    В корзину
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-600">В этой категории пока нет товаров.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>