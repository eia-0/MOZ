<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Заказ #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Информация о заказе --}}
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-2">Детали заказа</h3>
                <p><strong>Статус:</strong> <span id="order-status" class="font-bold text-blue-600">{{ $order->statusLabel() }}</span></p>
                <p><strong>Сумма:</strong> {{ $order->total_price }} руб. (доставка: {{ $order->delivery_fee }} руб.)</p>
                <p><strong>Тип получения:</strong> {{ $order->delivery_type === 'delivery' ? 'Доставка' : 'Самовывоз' }}</p>
                <p><strong>Ваш телефон:</strong> {{ $order->customer->phone ?? 'Не указан' }}</p>
                @if ($order->delivery_address)
                    <p><strong>Адрес доставки:</strong> {{ $order->delivery_address }}</p>
                    <p><strong>Инструкция:</strong> {{ $order->delivery_instructions ?? 'Не указана' }}</p>
                @endif
                <p class="text-sm text-gray-500 mt-2">Магазин: {{ $order->store->name }}</p>
            </div>

            {{-- Карта --}}
            <div id="map" style="height: 400px;" class="rounded shadow mb-6"></div>

            {{-- Статус-бар --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-semibold mb-3">Прогресс заказа</h3>
                <div class="flex items-center justify-between text-sm">
                    @php
                        if ($order->delivery_type === 'pickup') {
                            $steps = [
                                'new' => 'Новый',
                                'accepted' => 'Принят',
                                'preparing' => 'Готовится',
                                'ready' => 'Готов',
                                'delivered' => 'Выдан'
                            ];
                        } else {
                            $steps = [
                                'new' => 'Новый',
                                'accepted' => 'Принят',
                                'preparing' => 'Готовится',
                                'ready' => 'Готов',
                                'waiting_courier' => 'Ожидает курьера',
                                'courier_assigned' => 'Курьер назначен',
                                'in_transit' => 'В пути',
                                'delivered' => 'Доставлен'
                            ];
                        }
                        $stepKeys = array_keys($steps);
                        $currentStepIndex = array_search($order->status, $stepKeys);
                        if ($currentStepIndex === false) $currentStepIndex = 0;
                    @endphp
                    @foreach ($steps as $stepKey => $stepLabel)
                        @php $stepPos = array_search($stepKey, $stepKeys); @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center 
                                @if($stepPos <= $currentStepIndex) bg-blue-500 text-white @else bg-gray-300 @endif">
                                {{ $stepPos + 1 }}
                            </div>
                            <span class="mt-1 text-center">{{ $stepLabel }}</span>
                        </div>
                        @if (!$loop->last)
                            <div class="flex-1 h-1 mx-2 @if($stepPos < $currentStepIndex) bg-blue-500 @else bg-gray-300 @endif"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Цветные иконки
        const greenIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        const redIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
        const blueIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        var map = L.map('map').setView([55.763245, 60.707689], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var storeLat = {{ $order->store->latitude ?? 55.763245 }};
        var storeLng = {{ $order->store->longitude ?? 60.707689 }};
        L.marker([storeLat, storeLng], {icon: greenIcon}).addTo(map).bindPopup('Магазин: {{ $order->store->name }}');

        @if ($order->delivery_latitude && $order->delivery_longitude)
            L.marker([{{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}], {icon: redIcon})
                .addTo(map).bindPopup('{{ $order->delivery_address ?? "Доставка" }}');
        @endif

        var courierMarker = L.marker([0,0], {icon: blueIcon}).addTo(map);
        courierMarker.remove();

        window.Echo.channel('order.{{ $order->id }}')
            .listen('.OrderStatusChanged', (e) => {
                document.getElementById('order-status').innerText = e.statusLabel ?? e.status;
            })
            .listen('.CourierLocationUpdated', (e) => {
                if (!map.hasLayer(courierMarker)) courierMarker.addTo(map);
                courierMarker.setLatLng([e.latitude, e.longitude]);
            });
    </script>
</x-app-layout>