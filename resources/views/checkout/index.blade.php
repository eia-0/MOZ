<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Оформление заказа
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <h3 class="text-lg font-semibold mb-4">Ваш заказ из магазина «{{ $store->name }}»</h3>

                <ul class="mb-6">
                    @foreach ($cart as $productId => $qty)
                        @php $p = $products[$productId] @endphp
                        <li class="flex justify-between py-2 border-b">
                            <span>{{ $p->name }} x {{ $qty }}</span>
                            <span>{{ $p->price * $qty }} руб.</span>
                        </li>
                    @endforeach
                </ul>

                <p class="text-right text-lg font-bold">Товары: {{ $total }} руб.</p>
                @if ($store->delivery_fee > 0)
                    <p class="text-right text-gray-600">Доставка: {{ $deliveryFee }} руб.</p>
                    <p class="text-right text-xl font-bold">К оплате: {{ $total + $deliveryFee }} руб.</p>
                @endif
                <p class="text-sm text-gray-500 mt-2">Минимальная сумма заказа: {{ $store->min_order }} руб.</p>

                <form action="{{ route('checkout.store') }}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="store_id" value="{{ $store->id }}">
                    <input type="hidden" name="delivery_latitude" id="delivery_latitude" value="{{ old('delivery_latitude') }}">
                    <input type="hidden" name="delivery_longitude" id="delivery_longitude" value="{{ old('delivery_longitude') }}">

                    <div class="mb-4">
                        <label class="block font-medium">Способ получения</label>
                        <select name="delivery_type" id="delivery_type" class="w-full border rounded p-2 mt-1" required>
                            <option value="pickup" {{ old('delivery_type') == 'pickup' ? 'selected' : '' }}>Самовывоз</option>
                            <option value="delivery" {{ old('delivery_type') == 'delivery' ? 'selected' : '' }}>Доставка</option>
                        </select>
                    </div>

                    <div id="delivery_fields" style="display: {{ old('delivery_type') == 'delivery' ? 'block' : 'none' }};">
                        {{-- Сохранённые адреса --}}
                        @if ($addresses->isNotEmpty())
                        <div class="mb-4">
                            <label class="block font-medium">Сохранённые адреса</label>
                            <select id="saved_address" class="w-full border rounded p-2 mt-1">
                                <option value="">-- Новый адрес --</option>
                                @foreach ($addresses as $addr)
                                    <option value="{{ $addr->id }}"
                                        data-street="{{ $addr->street }}"
                                        data-house="{{ $addr->house }}"
                                        data-floor="{{ $addr->floor }}"
                                        data-apartment="{{ $addr->apartment }}"
                                        data-entrance="{{ $addr->entrance }}"
                                        data-lat="{{ $addr->latitude }}"
                                        data-lng="{{ $addr->longitude }}">
                                        {{ $addr->full_address }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <p class="text-sm text-gray-600 mb-2">Кликните по карте, чтобы указать точку доставки. Адрес подставится автоматически.</p>
                        <div id="checkout-map" style="height: 300px; width: 100%;" class="rounded mb-4"></div>

                        {{-- Улица и дом в одной строке --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block font-medium">Улица</label>
                                <input type="text" name="street" id="street" value="{{ old('street') }}" class="w-full border rounded p-2 mt-1" placeholder="Улица">
                            </div>
                            <div>
                                <label class="block font-medium">Номер дома</label>
                                <input type="text" name="house" id="house" value="{{ old('house') }}" class="w-full border rounded p-2 mt-1" placeholder="Дом">
                            </div>
                        </div>

                        {{-- Подъезд, этаж, квартира/офис в одной строке --}}
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block font-medium">Подъезд</label>
                                <input type="text" name="entrance" id="entrance" value="{{ old('entrance') }}" class="w-full border rounded p-2 mt-1" placeholder="Подъезд">
                            </div>
                            <div>
                                <label class="block font-medium">Этаж</label>
                                <input type="number" name="floor" id="floor" value="{{ old('floor') }}" class="w-full border rounded p-2 mt-1" placeholder="Этаж">
                            </div>
                            <div>
                                <label class="block font-medium">Квартира/Офис</label>
                                <input type="text" name="apartment" id="apartment" value="{{ old('apartment') }}" class="w-full border rounded p-2 mt-1" placeholder="Квартира/офис">
                            </div>
                        </div>

                        {{-- Телефон с маской --}}
                        <div class="mb-4">
                            <label class="block font-medium">Контактный телефон</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', auth()->user()->phone) }}"
                                   class="w-full border rounded p-2 mt-1" placeholder="+7 (___) ___-__-__"
                                   pattern="\+7\s?\(\d{3}\)\s?\d{3}-\d{2}-\d{2}" title="Формат: +7 (999) 123-45-67">
                        </div>

                        {{-- Способ передачи --}}
                        <div class="mb-4">
                            <label class="block font-medium mb-2">Способ передачи</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="leave_at_door" value="1" class="sr-only peer" {{ old('leave_at_door') ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700">Оставить у двери</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Если выключено — передать в руки</p>
                        </div>
                    </div>

                    <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600"
                            onclick="this.disabled=true; this.form.submit();">
                        Подтвердить заказ
                    </button>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Показать/скрыть поля доставки
        var deliveryType = document.getElementById('delivery_type');
        var deliveryFields = document.getElementById('delivery_fields');

        deliveryType.addEventListener('change', function() {
            deliveryFields.style.display = this.value === 'delivery' ? 'block' : 'none';
            if (this.value === 'delivery') {
                setTimeout(function() { checkoutMap.invalidateSize(); }, 150);
            }
        });

        // Иконка для точки доставки (красный маркер)
        var redIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Карта с ограничением Озёрска
        var checkoutMap = L.map('checkout-map', {
            maxBounds: [[55.72, 60.65], [55.80, 60.75]],
            minZoom: 13,
            maxZoom: 17
        }).setView([55.763245, 60.707689], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(checkoutMap);

        var deliveryMarker = L.marker([55.763245, 60.707689], {icon: redIcon, draggable: true}).addTo(checkoutMap);

        function updateAddressFields(lat, lng, street, house) {
            document.getElementById('delivery_latitude').value = lat;
            document.getElementById('delivery_longitude').value = lng;
            if (street !== undefined) document.getElementById('street').value = street;
            if (house !== undefined) document.getElementById('house').value = house;
        }

        function geocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ru`)
                .then(res => res.json())
                .then(data => {
                    if (data.address) {
                        var road = data.address.road || data.address.street || data.address.pedestrian || '';
                        var house = data.address.house_number || '';
                        document.getElementById('street').value = road;
                        document.getElementById('house').value = house;
                        updateAddressFields(lat, lng, road, house);
                    }
                })
                .catch(err => console.log('Ошибка геокодирования:', err));
        }

        checkoutMap.on('click', function(e) {
            deliveryMarker.setLatLng(e.latlng);
            geocode(e.latlng.lat, e.latlng.lng);
        });

        deliveryMarker.on('dragend', function(e) {
            var pos = deliveryMarker.getLatLng();
            geocode(pos.lat, pos.lng);
        });

        // Обработчик выбора сохранённого адреса
        var savedAddressSelect = document.getElementById('saved_address');
        if (savedAddressSelect) {
            savedAddressSelect.addEventListener('change', function() {
                var selected = this.options[this.selectedIndex];
                if (selected.value) {
                    var lat = parseFloat(selected.dataset.lat);
                    var lng = parseFloat(selected.dataset.lng);
                    document.getElementById('street').value = selected.dataset.street;
                    document.getElementById('house').value = selected.dataset.house;
                    document.getElementById('floor').value = selected.dataset.floor || '';
                    document.getElementById('apartment').value = selected.dataset.apartment || '';
                    document.getElementById('entrance').value = selected.dataset.entrance || '';
                    updateAddressFields(lat, lng, selected.dataset.street, selected.dataset.house);
                    deliveryMarker.setLatLng([lat, lng]);
                    checkoutMap.setView([lat, lng], 16);
                } else {
                    // Новый адрес – очищаем поля и сбрасываем карту
                    document.getElementById('street').value = '';
                    document.getElementById('house').value = '';
                    document.getElementById('floor').value = '';
                    document.getElementById('apartment').value = '';
                    document.getElementById('entrance').value = '';
                    checkoutMap.setView([55.763245, 60.707689], 14);
                    deliveryMarker.setLatLng([55.763245, 60.707689]);
                    updateAddressFields(55.763245, 60.707689);
                }
            });
        }

        // Инициализация при загрузке
        var initLat = document.getElementById('delivery_latitude').value;
        var initLng = document.getElementById('delivery_longitude').value;
        if (deliveryType.value === 'delivery') {
            deliveryFields.style.display = 'block';
            setTimeout(function() { checkoutMap.invalidateSize(); }, 150);
            if (initLat && initLng) {
                deliveryMarker.setLatLng([initLat, initLng]);
                checkoutMap.setView([initLat, initLng], 16);
            }
        }

        // Маска для телефона
        var phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                var x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                e.target.value = '+7' + (x[2] ? '(' + x[2] + ')' : '') + (x[3] ? ' ' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
            });
        }
    </script>
</x-app-layout>