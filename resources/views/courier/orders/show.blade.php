<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Заказ #{{ $order->id }} (доставить)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6 mb-4">
                <p><strong>Откуда:</strong> {{ $order->store->name }}</p>
                <p><strong>Куда:</strong> {{ $order->delivery_address }}</p>
                <p><strong>Клиент:</strong> {{ $order->customer->name }}, тел. {{ $order->customer->phone ?? '—' }}</p>
                <p><strong>Инструкция:</strong> {{ $order->delivery_instructions ?? '—' }}</p>
                <p><strong>Статус:</strong> <span id="order-status">{{ $order->statusLabel() }}</span></p>
            </div>

            <div id="map" style="height: 400px;" class="mb-4"></div>

            <div class="bg-white shadow rounded-lg p-6">
                @if ($order->courier_id === auth()->id())
                    @if ($order->status === 'courier_assigned')
                        <form action="{{ route('courier.orders.update-status', $order) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="in_transit">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded"
                                    onclick="this.disabled=true; this.form.submit();">Я забрал заказ</button>
                        </form>
                    @elseif ($order->status === 'in_transit')
                        <form action="{{ route('courier.orders.update-status', $order) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded"
                                    onclick="this.disabled=true; this.form.submit();">Доставил</button>
                        </form>
                    @else
                        <p class="text-gray-500">Нет доступных действий.</p>
                    @endif
                @elseif (is_null($order->courier_id) && $order->status === 'waiting_courier')
                    <form action="{{ route('courier.orders.accept', $order) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded"
                                onclick="this.disabled=true; this.form.submit();">Принять заказ</button>
                    </form>
                @else
                    <p class="text-gray-500">Вы не можете управлять этим заказом.</p>
                @endif
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
        L.marker([storeLat, storeLng], {icon: greenIcon}).addTo(map).bindPopup('Магазин: {{ $order->store->name }}');

        @if ($order->delivery_latitude && $order->delivery_longitude)
            L.marker([{{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}], {icon: redIcon})
                .addTo(map).bindPopup('{{ $order->delivery_address ?? "Доставка" }}');
        @else
            L.marker([55.763245, 60.707689], {icon: redIcon}).addTo(map).bindPopup('Предполагаемая доставка');
        @endif

        // Геолокация курьера (иконка blue)
        @if($order->courier_id === auth()->id() && in_array($order->status, ['courier_assigned','in_transit']))
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(function(pos) {
                axios.post('{{ route("courier.location.update") }}', {
                    order_id: {{ $order->id }},
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    _token: '{{ csrf_token() }}'
                });
                // Показать курьера на карте (если ещё не отображается, можно добавить маркер)
                if (typeof courierMarker === 'undefined') {
                    courierMarker = L.marker([pos.coords.latitude, pos.coords.longitude], {icon: blueIcon}).addTo(map);
                } else {
                    courierMarker.setLatLng([pos.coords.latitude, pos.coords.longitude]);
                }
            }, null, { enableHighAccuracy: true });
        }
        @endif
    </script>
</x-app-layout>