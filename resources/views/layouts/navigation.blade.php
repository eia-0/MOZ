<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links (desktop) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(!auth()->user()?->isCourier())
                        <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                            Каталог
                        </x-nav-link>
                    @endif

                    @auth
                        @if(auth()->user()->isStore())
                            <x-nav-link :href="route('store.dashboard')" :active="request()->routeIs('store.dashboard')">Главное</x-nav-link>
                            <x-nav-link :href="route('store.products.index')" :active="request()->routeIs('store.products.*')">Товары</x-nav-link>
                            <x-nav-link :href="route('store.categories.index')" :active="request()->routeIs('store.categories.*')">Категории</x-nav-link>
                            <x-nav-link :href="route('store.suppliers.index')" :active="request()->routeIs('store.suppliers.*')">Поставщики</x-nav-link>
                            <x-nav-link :href="route('store.orders')" :active="request()->routeIs('store.orders.*')">Заказы</x-nav-link>
                            <x-nav-link :href="route('store.settings')" :active="request()->routeIs('store.settings')">Настройки</x-nav-link>
                        @elseif(auth()->user()->isCourier())
                            <x-nav-link :href="route('courier.orders')" :active="request()->routeIs('courier.*')">Заказы на доставку</x-nav-link>
                        @elseif(auth()->user()->isCustomer())
                            <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')">
                                🛒 Корзина
                                @php $cartCount = array_sum(session('cart', [])); @endphp
                                @if($cartCount > 0)
                                    <span class="inline-flex items-center justify-center w-5 h-5 ms-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $cartCount }}</span>
                                @endif
                            </x-nav-link>
                            <x-nav-link :href="route('customer.orders')" :active="request()->routeIs('customer.*')">Мои заказы</x-nav-link>
                        @endif
                    @endauth

                    @guest
                        <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')">
                            🛒 Корзина
                            @php $cartCount = array_sum(session('cart', [])); @endphp
                            @if($cartCount > 0)
                                <span class="inline-flex items-center justify-center w-5 h-5 ms-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $cartCount }}</span>
                            @endif
                        </x-nav-link>
                    @endguest
                </div>
            </div>

            <!-- Settings Dropdown (desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Профиль</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Выйти</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 underline">Войти</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 underline">Регистрация</a>
                    @endif
                @endauth
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (mobile dropdown) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(!auth()->user()?->isCourier())
                <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Каталог</x-responsive-nav-link>
            @endif
            @auth
                @if(auth()->user()->isStore())
                    <x-responsive-nav-link :href="route('store.dashboard')" :active="request()->routeIs('store.dashboard')">Главное</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('store.products.index')" :active="request()->routeIs('store.products.*')">Товары</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('store.categories.index')" :active="request()->routeIs('store.categories.*')">Категории</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('store.suppliers.index')" :active="request()->routeIs('store.suppliers.*')">Поставщики</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('store.orders')" :active="request()->routeIs('store.orders.*')">Заказы</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('store.settings')" :active="request()->routeIs('store.settings')">Настройки</x-responsive-nav-link>
                @elseif(auth()->user()->isCourier())
                    <x-responsive-nav-link :href="route('courier.orders')" :active="request()->routeIs('courier.*')">Заказы на доставку</x-responsive-nav-link>
                @elseif(auth()->user()->isCustomer())
                    <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')">
                        🛒 Корзина
                        @php $cartCount = array_sum(session('cart', [])); @endphp
                        @if($cartCount > 0)
                            <span class="inline-flex items-center justify-center w-5 h-5 ms-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $cartCount }}</span>
                        @endif
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('customer.orders')" :active="request()->routeIs('customer.*')">Мои заказы</x-responsive-nav-link>
                @endif
            @endauth
            @guest
                <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')">
                    🛒 Корзина
                    @php $cartCount = array_sum(session('cart', [])); @endphp
                    @if($cartCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 ms-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $cartCount }}</span>
                    @endif
                </x-responsive-nav-link>
            @endguest
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">Профиль</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Выйти</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('login')">Войти</x-responsive-nav-link>
                    @if (Route::has('register'))
                        <x-responsive-nav-link :href="route('register')">Регистрация</x-responsive-nav-link>
                    @endif
                </div>
            </div>
        @endauth
    </div>
</nav>

{{-- ===== МОБИЛЬНАЯ НИЖНЯЯ ПАНЕЛЬ (покупатель/гость) ===== --}}
@if(!auth()->user()?->isStore() && !auth()->user()?->isCourier())
    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="flex justify-around items-center py-2">
            <a href="{{ route('home') }}" class="flex flex-col items-center text-gray-600 {{ request()->routeIs('home') ? 'text-blue-600' : '' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span class="text-xs mt-1">Каталог</span>
            </a>
            {{-- Корзина с относительным позиционированием для счётчика --}}
            <a href="{{ route('cart.index') }}" class="relative flex flex-col items-center text-gray-600 {{ request()->routeIs('cart.*') ? 'text-blue-600' : '' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                <span class="text-xs mt-1">Корзина</span>
                @php $cartCount = array_sum(session('cart', [])); @endphp
                @if($cartCount > 0)
                    <span class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </a>
            @auth
                <a href="{{ route('customer.orders') }}" class="flex flex-col items-center text-gray-600 {{ request()->routeIs('customer.*') ? 'text-blue-600' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-xs mt-1">Мои заказы</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-xs mt-1">Войти</span>
                </a>
            @endauth
        </div>
    </div>
@endif