<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Оформление заказа</h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ваш заказ из «{{ $store->name }}»</h3>
                <ul class="mb-4">
                    @foreach ($cart as $productId => $qty)
                        @php $p = $products[$productId] @endphp
                        <li class="flex justify-between py-2 border-b text-sm">
                            <span>{{ $p->name }} x {{ $qty }}</span>
                            <span>{{ $p->price * $qty }} ₽</span>
                        </li>
                    @endforeach
                </ul>

                <div class="text-right text-sm">
                    <p class="font-medium">Товары: {{ $total }} ₽</p>
                    @if ($store->delivery_fee > 0)
                        <p class="text-gray-600">Доставка: {{ $deliveryFee }} ₽</p>
                        <p class="font-bold text-lg text-green-600">К оплате: {{ $total + $deliveryFee }} ₽</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-1">Минимальный заказ: {{ $store->min_order }} ₽</p>
                </div>

                <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="store_id" value="{{ $store->id }}">
                    <input type="hidden" name="delivery_latitude" id="delivery_latitude"
                           value="{{ old('delivery_latitude', $lastAddress->latitude ?? '') }}">
                    <input type="hidden" name="delivery_longitude" id="delivery_longitude"
                           value="{{ old('delivery_longitude', $lastAddress->longitude ?? '') }}">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Способ получения</label>
                        <select name="delivery_type" id="delivery_type"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="pickup" {{ old('delivery_type') == 'pickup' ? 'selected' : '' }}>Самовывоз</option>
                            <option value="delivery" {{ old('delivery_type') == 'delivery' ? 'selected' : '' }}>Доставка</option>
                        </select>
                    </div>

                    <div id="delivery_fields" style="display: {{ old('delivery_type') == 'delivery' ? 'block' : 'none' }};">
                        @if ($addresses->isNotEmpty())
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Сохранённые адреса</label>
                                <select name="address_id" id="saved_address"
                                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- Новый адрес --</option>
                                    @foreach ($addresses as $addr)
                                        @php
                                            $hasCoords = !is_null($addr->latitude) && !is_null($addr->longitude);
                                        @endphp
                                        <option value="{{ $addr->id }}"
                                            data-street="{{ $addr->street }}"
                                            data-house="{{ $addr->house }}"
                                            data-floor="{{ $addr->floor }}"
                                            data-apartment="{{ $addr->apartment }}"
                                            data-entrance="{{ $addr->entrance }}"
                                            data-lat="{{ $addr->latitude }}"
                                            data-lng="{{ $addr->longitude }}"
                                            {{ (old('address_id') == $addr->id || (!old('address_id') && $lastAddress && $lastAddress->id == $addr->id && $hasCoords)) ? 'selected' : '' }}
                                            {{ !$hasCoords ? 'disabled title="Нет координат, выберите точку на карте"' : '' }}>
                                            {{ $addr->full_address }} @if(!$hasCoords) (без координат) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <p class="text-sm text-gray-600 mb-2">
                            <span id="map-hint">Кликните по карте, чтобы указать точку доставки.</span>
                        </p>
                        <div id="checkout-map" style="height: 300px; width: 100%;" class="rounded-xl mb-4"></div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Улица</label>
                                <input type="text" name="street" id="street"
                                       value="{{ old('street', $lastAddress->street ?? '') }}"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Улица">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Дом</label>
                                <input type="text" name="house" id="house"
                                       value="{{ old('house', $lastAddress->house ?? '') }}"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Дом">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Подъезд</label>
                                <input type="text" name="entrance" id="entrance"
                                       value="{{ old('entrance', $lastAddress->entrance ?? '') }}"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Подъезд">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Этаж</label>
                                <input type="number" name="floor" id="floor"
                                       value="{{ old('floor', $lastAddress->floor ?? '') }}"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Этаж">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Квартира</label>
                                <input type="text" name="apartment" id="apartment"
                                       value="{{ old('apartment', $lastAddress->apartment ?? '') }}"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="Квартира">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Телефон</label>
                            <input type="tel" name="phone" id="phone"
                                   value="{{ old('phone', auth()->user()->phone) }}"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                   placeholder="+7 (___) ___-__-__">
                        </div>
                        <div class="mb-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="leave_at_door" value="1" class="sr-only peer"
                                       {{ old('leave_at_door') ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700">Оставить у двери</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Если выключено — передать в руки</p>
                        </div>
                    </div>

                    <button type="submit" id="submit-button"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-xl transition shadow-md hover:shadow-lg mt-4">
                        Подтвердить заказ
                    </button>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const form = document.getElementById('checkout-form');
        const deliveryType = document.getElementById('delivery_type');
        const deliveryFields = document.getElementById('delivery_fields');
        const latInput = document.getElementById('delivery_latitude');
        const lngInput = document.getElementById('delivery_longitude');
        const savedSelect = document.getElementById('saved_address');
        const mapHint = document.getElementById('map-hint');

        // Показать/скрыть поля доставки
        deliveryType.addEventListener('change', function() {
            deliveryFields.style.display = this.value === 'delivery' ? 'block' : 'none';
            if (this.value === 'delivery') {
                setTimeout(() => checkoutMap.invalidateSize(), 150);
            }
        });

        // Инициализация карты
        const checkoutMap = L.map('checkout-map', {
            maxBounds: [[55.72, 60.65], [55.80, 60.75]],
            minZoom: 13,
            maxZoom: 17
        }).setView([55.763245, 60.707689], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(checkoutMap);

        let deliveryMarker = L.marker([55.763245, 60.707689], { draggable: true }).addTo(checkoutMap);
        let manualCoordsSet = false;

        // Установить начальную позицию маркера, если есть координаты
        function initMarker() {
            const lat = latInput.value;
            const lng = lngInput.value;
            if (lat && lng && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))) {
                deliveryMarker.setLatLng([parseFloat(lat), parseFloat(lng)]);
                checkoutMap.setView([parseFloat(lat), parseFloat(lng)], 16);
                manualCoordsSet = true;
            } else {
                // Сбрасываем на центр Озёрска
                deliveryMarker.setLatLng([55.763245, 60.707689]);
                checkoutMap.setView([55.763245, 60.707689], 14);
                manualCoordsSet = false;
                if (deliveryType.value === 'delivery') {
                    mapHint.innerHTML = 'Кликните по карте, чтобы указать точку доставки (обязательно)';
                }
            }
        }

        function updateAddressFields(lat, lng, street, house) {
            latInput.value = lat;
            lngInput.value = lng;
            if (street !== undefined) document.getElementById('street').value = street;
            if (house !== undefined) document.getElementById('house').value = house;
            // При любом изменении координат вручную сбрасываем выбор сохранённого адреса
            if (savedSelect) savedSelect.value = '';
            manualCoordsSet = true;
            mapHint.innerHTML = 'Координаты установлены';
        }

        function geocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ru`)
                .then(res => res.json())
                .then(data => {
                    if (data.address) {
                        const road = data.address.road || data.address.street || data.address.pedestrian || '';
                        const house = data.address.house_number || '';
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
            const pos = deliveryMarker.getLatLng();
            geocode(pos.lat, pos.lng);
        });

        // Обработчик выбора сохранённого адреса
        if (savedSelect) {
            savedSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                if (selected.value) {
                    const lat = selected.dataset.lat;
                    const lng = selected.dataset.lng;
                    // Проверка, что координаты есть и не пустые
                    if (lat && lng && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))) {
                        const latF = parseFloat(lat);
                        const lngF = parseFloat(lng);
                        document.getElementById('street').value = selected.dataset.street || '';
                        document.getElementById('house').value = selected.dataset.house || '';
                        document.getElementById('floor').value = selected.dataset.floor || '';
                        document.getElementById('apartment').value = selected.dataset.apartment || '';
                        document.getElementById('entrance').value = selected.dataset.entrance || '';
                        updateAddressFields(latF, lngF, selected.dataset.street, selected.dataset.house);
                        deliveryMarker.setLatLng([latF, lngF]);
                        checkoutMap.setView([latF, lngF], 16);
                    } else {
                        // Адрес без координат – сбросить выбор и предупредить
                        this.value = '';
                        latInput.value = '';
                        lngInput.value = '';
                        mapHint.innerHTML = 'Этот адрес не содержит координат. Кликните по карте, чтобы указать точку.';
                        alert('Этот адрес не содержит координат. Пожалуйста, укажите точку на карте вручную.');
                        initMarker();
                    }
                } else {
                    // Новый адрес – очистить координаты
                    latInput.value = '';
                    lngInput.value = '';
                    mapHint.innerHTML = 'Кликните по карте, чтобы указать точку доставки (обязательно)';
                    manualCoordsSet = false;
                    deliveryMarker.setLatLng([55.763245, 60.707689]);
                    checkoutMap.setView([55.763245, 60.707689], 14);
                }
            });

            // Сброс сохранённого адреса при ручном вводе полей
            ['street', 'house', 'floor', 'apartment', 'entrance'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        savedSelect.value = '';
                        latInput.value = '';
                        lngInput.value = '';
                        manualCoordsSet = false;
                        mapHint.innerHTML = 'Кликните по карте, чтобы указать точку доставки (обязательно)';
                    });
                }
            });
        }

        // Проверка перед отправкой формы
        form.addEventListener('submit', function(e) {
            if (deliveryType.value === 'delivery') {
                if (!latInput.value || !lngInput.value || isNaN(parseFloat(latInput.value)) || isNaN(parseFloat(lngInput.value))) {
                    e.preventDefault();
                    alert('Пожалуйста, укажите точку доставки на карте.');
                    mapHint.innerHTML = 'Кликните по карте, чтобы указать точку доставки (обязательно)';
                    return false;
                }
            }
            // Блокируем кнопку, чтобы избежать двойной отправки
            const btn = document.getElementById('submit-button');
            btn.disabled = true;
            btn.innerText = 'Отправка...';
        });

        // Инициализация при загрузке
        if (deliveryType.value === 'delivery') {
            deliveryFields.style.display = 'block';
            setTimeout(() => checkoutMap.invalidateSize(), 150);
            initMarker();
        }

        // Маска телефона
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                const x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                e.target.value = '+7' + (x[2] ? '(' + x[2] + ')' : '') + (x[3] ? ' ' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
            });
        }
    </script>
</x-app-layout>