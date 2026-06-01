<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold text-gray-800">
                Каталог
            </h2>
            @if ($store)
                <div class="flex items-center gap-2 mt-2 sm:mt-0">
                    <span class="text-sm text-gray-600">{{ $store->name }}</span>
                    @if ($store->phone)
                        <span class="text-sm text-gray-500">📞 {{ $store->phone }}</span>
                    @endif
                    @if ($isOpen)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            🟢 Открыто
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            🔴 Закрыто
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Активные заказы --}}
            @auth
                @if(auth()->user()->isCustomer() && $activeOrders->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Мои активные заказы</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($activeOrders as $order)
                                @php
                                    $statusColors = [
                                        'new'               => 'border-l-green-500 bg-green-50',
                                        'accepted'          => 'border-l-blue-500 bg-blue-50',
                                        'preparing'         => 'border-l-yellow-500 bg-yellow-50',
                                        'ready'             => 'border-l-indigo-500 bg-indigo-50',
                                        'waiting_courier'   => 'border-l-orange-500 bg-orange-50',
                                        'courier_assigned'  => 'border-l-purple-500 bg-purple-50',
                                        'in_transit'        => 'border-l-red-500 bg-red-50',
                                    ];
                                    $borderClass = $statusColors[$order->status] ?? 'border-l-gray-300 bg-gray-50';
                                @endphp
                                <a href="{{ route('customer.orders.show', $order) }}" class="block bg-white rounded-xl shadow-sm border-l-4 {{ $borderClass }} p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-gray-800">Заказ #{{ $order->id }}</p>
                                            <p class="text-sm text-gray-600">{{ $order->store->name }}</p>
                                            <p class="text-sm text-gray-700 mt-1">Статус: <span class="font-medium">{{ $order->statusLabel() }}</span></p>
                                        </div>
                                        <span class="text-sm font-bold text-green-600">{{ $order->total_price }} ₽</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endauth

            {{-- Категории --}}
            @if ($categories->isNotEmpty())
                <div class="mb-6">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('home') }}"
                           class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !$categoryId ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                            Все
                        </a>
                        @foreach ($categories as $cat)
                            @php
                                $isActive = $categoryId == $cat->id || $cat->children->pluck('id')->contains($categoryId);
                            @endphp
                            <a href="{{ route('home', ['category_id' => $cat->id]) }}"
                               class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $isActive ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                    @if ($categoryId)
                        @php
                            $activeParent = $categories->firstWhere('id', $categoryId);
                            if (!$activeParent) {
                                $childCategory = \App\Models\Category::find($categoryId);
                                $activeParent = $childCategory ? $childCategory->parent : null;
                            }
                        @endphp
                        @if ($activeParent && $activeParent->children->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($activeParent->children as $child)
                                    <a href="{{ route('home', ['category_id' => $child->id]) }}"
                                       class="px-3 py-1 rounded-full text-xs font-medium transition-colors {{ $categoryId == $child->id ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            {{-- Товары --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                @foreach ($products as $product)
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col group">
                        <a href="{{ route('product.show', $product) }}" class="block aspect-square overflow-hidden bg-gray-100">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </a>
                        <div class="p-3 sm:p-4 flex flex-col flex-grow">
                            <h3 class="font-semibold text-sm sm:text-base text-gray-800 line-clamp-2 mb-1">
                                <a href="{{ route('product.show', $product) }}" class="hover:text-blue-600 transition-colors">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            @if($product->weight)
                                <p class="text-xs text-gray-500">{{ $product->weight }} г</p>
                            @endif
                            <div class="mt-auto pt-2">
                                <div class="flex items-baseline justify-between mb-2">
                                    <span class="text-lg sm:text-xl font-bold text-green-600">{{ $product->price }} ₽</span>
                                    @if (!is_null($product->stock))
                                        <span class="text-xs text-gray-600">{{ $product->stock > 0 ? $product->stock . ' шт.' : 'закончился' }}</span>
                                    @endif
                                </div>
                                @php $inCart = isset($cart[$product->id]); $qty = $cart[$product->id] ?? 0; @endphp
                                @if ($inCart)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-1">
                                            <form action="{{ route('cart.update', $product->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="quantity" value="{{ $qty - 1 }}">
                                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors">
                                                    −
                                                </button>
                                            </form>
                                            <span class="font-semibold w-6 text-center text-sm">{{ $qty }}</span>
                                            <form action="{{ route('cart.add', $product) }}" method="POST">
                                                @csrf
                                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition-colors">
                                                    +
                                                </button>
                                            </form>
                                        </div>
                                        <form action="{{ route('cart.remove', $product->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="text-gray-400 hover:text-red-500 transition-colors text-xl leading-none">&times;</button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('cart.add', $product) }}" method="POST">
                                        @csrf
                                        <button class="w-full py-2 px-3 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-xl transition-colors shadow-sm hover:shadow-md">
                                            В корзину
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>