<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Заказ #{{ $order->id }}</h2>
            <a href="{{ route('customer.orders') }}" class="text-sm text-blue-600 hover:underline">← Все заказы</a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600">Статус</p>
                        <p class="font-semibold text-blue-600" id="order-status-text">{{ $order->statusLabel() }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Сумма</p>
                        <p class="font-semibold">{{ $order->total_price }} ₽</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Тип получения</p>
                        <p>{{ $order->delivery_type === 'delivery' ? 'Доставка' : 'Самовывоз' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Магазин</p>
                        <p>{{ $order->store->name }}</p>
                    </div>
                    @if ($order->delivery_address)
                        <div class="col-span-2">
                            <p class="text-gray-600">Адрес доставки</p>
                            <p>{{ $order->delivery_address }}</p>
                        </div>
                    @endif
                    @if ($order->courier)
                        <div class="col-span-2">
                            <p class="text-gray-600">Курьер</p>
                            <p>{{ $order->courier->name }} @if($order->courier->phone) ({{ $order->courier->phone }}) @endif</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Прогресс заказа</h3>
                <div class="flex items-center justify-between text-xs sm:text-sm" id="status-bar">
                    @include('customer.orders.partials.status-bar', ['order' => $order])
                </div>
            </div>

            <div id="map" style="height: 300px;" class="rounded-2xl shadow-sm mb-6"></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([55.763245, 60.707689], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([{{ $order->store->latitude ?? 55.763245 }}, {{ $order->store->longitude ?? 60.707689 }}]).addTo(map).bindPopup('{{ $order->store->name }}');
        @if ($order->delivery_latitude && $order->delivery_longitude)
            L.marker([{{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}]).addTo(map).bindPopup('{{ $order->delivery_address }}');
        @endif

        // Polling для обновления статуса
        function updateStatus() {
            fetch('{{ route("customer.orders.status", $order) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.status !== '{{ $order->status }}') {
                        document.getElementById('order-status-text').innerText = data.statusLabel;
                        // Обновить статус-бар можно через перезагрузку части страницы
                        location.reload(); // самое простое – перезагрузить страницу
                    }
                });
        }
        setInterval(updateStatus, 5000); // опрос каждые 5 секунд
    </script>
</x-app-layout>