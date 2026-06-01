<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Заказ #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6 mb-4">
                <p><strong>Клиент:</strong> {{ $order->customer->name }} ({{ $order->customer->phone ?? '—' }})</p>
                @if ($order->courier)
                    <p><strong>Курьер:</strong> {{ $order->courier->name }} ({{ $order->courier->phone ?? '—' }})</p>
                @endif
                <p><strong>Тип:</strong> {{ $order->delivery_type === 'delivery' ? 'Доставка' : 'Самовывоз' }}</p>
                @if ($order->delivery_address)
                    <p><strong>Адрес:</strong> {{ $order->delivery_address }}</p>
                    <p><strong>Инструкция:</strong> {{ $order->delivery_instructions ?? '—' }}</p>
                @endif
                <p><strong>Статус:</strong> <span id="order-status">{{ $order->statusLabel() }}</span></p>
                <p><strong>Сумма:</strong> {{ $order->total_price }} руб.</p>
            </div>

            <div id="map" style="height: 400px;" class="mb-4 rounded shadow"></div>

            <div class="bg-white shadow rounded-lg p-6 mb-4">
                <h3 class="font-semibold mb-2">Сменить статус</h3>
                @php
                    $transitions = [
                        'new' => ['accepted' => 'Принять', 'cancelled' => 'Отменить'],
                        'accepted' => ['preparing' => 'Готовится'],
                        'preparing' => ['ready' => 'Готов'],
                        'ready' => $order->delivery_type === 'pickup'
                                    ? ['delivered' => 'Выдан']
                                    : ['waiting_courier' => 'Ожидает курьера'],
                    ];
                    $currentTransitions = $transitions[$order->status] ?? [];
                @endphp
                @if (!empty($currentTransitions))
                    <form action="{{ route('store.orders.update-status', $order) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="status" class="border rounded p-2">
                            @foreach ($currentTransitions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded ml-2"
                                onclick="this.disabled=true; this.form.submit();">Обновить</button>
                    </form>
                @else
                    <p class="text-gray-500">Нет доступных действий.</p>
                @endif
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('store.orders.destroy', $order) }}" method="POST"
                      onsubmit="return confirm('Вы уверены, что хотите удалить заказ #{{ $order->id }}? Это действие нельзя отменить.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                            onclick="this.disabled=true; this.form.submit();">
                        Удалить заказ
                    </button>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
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
        var deliveryLat = {{ $order->delivery_latitude ?? 55.763245 }};
        var deliveryLng = {{ $order->delivery_longitude ?? 60.707689 }};

        L.marker([storeLat, storeLng], {icon: greenIcon}).addTo(map).bindPopup('Магазин');
        L.marker([deliveryLat, deliveryLng], {icon: redIcon}).addTo(map).bindPopup('{{ $order->delivery_address ?? "Адрес доставки" }}');

        var courierMarker = L.marker([0,0], {icon: blueIcon}).addTo(map);
        map.removeLayer(courierMarker);

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