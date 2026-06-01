<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Настройки магазина: {{ $store->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('store.settings.update') }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="block font-medium">Название магазина</label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Описание</label>
                    <textarea name="description" class="w-full border rounded p-2">{{ old('description', $store->description) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Широта (latitude)</label>
                    <input type="text" name="latitude" value="{{ old('latitude', $store->latitude) }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Долгота (longitude)</label>
                    <input type="text" name="longitude" value="{{ old('longitude', $store->longitude) }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Стоимость доставки (руб.)</label>
                    <input type="number" name="delivery_fee" value="{{ old('delivery_fee', $store->delivery_fee) }}" class="w-full border rounded p-2" required min="0" step="0.01">
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Минимальная сумма заказа (руб.)</label>
                    <input type="number" name="min_order" value="{{ old('min_order', $store->min_order) }}" class="w-full border rounded p-2" required min="0" step="0.01">
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Бесплатная доставка от (руб.) <span class="text-gray-400">(оставьте пустым, если всегда платно)</span></label>
                    <input type="number" name="free_delivery_from" value="{{ old('free_delivery_from', $store->free_delivery_from) }}" class="w-full border rounded p-2" min="0" step="0.01" placeholder="Например, 1500">
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Контактный телефон магазина</label>
                    <input type="tel" name="phone" id="store_phone" value="{{ old('phone', $store->phone) }}"
                           class="w-full border rounded p-2" placeholder="+7 (___) ___-__-__">
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Адрес магазина</label>
                    <input type="text" name="address" value="{{ old('address', $store->address) }}"
                           class="w-full border rounded p-2" placeholder="ул. Ленина, 10">
                </div>

                {{-- График работы --}}
                <div class="mb-6">
                    <label class="block font-medium mb-2">График работы (часовой пояс Озёрска UTC+05:00)</label>
                    <table class="w-full text-sm border rounded">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">День</th>
                                <th class="p-2 text-left">Открытие</th>
                                <th class="p-2 text-left">Закрытие</th>
                                <th class="p-2 text-center">Выходной</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $days = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
                                $workingHours = old('working_hours', $store->working_hours ?? []);
                            @endphp
                            @foreach ($days as $index => $day)
                                @php
                                    $dayData = $workingHours[$index] ?? ['open' => '', 'close' => ''];
                                    $isDayOff = !empty($dayData['open']) ? false : true;
                                @endphp
                                <tr class="border-t">
                                    <td class="p-2">{{ $day }}</td>
                                    <td class="p-2">
                                        <input type="time" name="working_hours[{{ $index }}][open]"
                                               value="{{ old("working_hours.{$index}.open", $dayData['open']) }}"
                                               class="w-24 border rounded p-1"
                                               {{ $isDayOff ? 'disabled' : '' }}>
                                    </td>
                                    <td class="p-2">
                                        <input type="time" name="working_hours[{{ $index }}][close]"
                                               value="{{ old("working_hours.{$index}.close", $dayData['close']) }}"
                                               class="w-24 border rounded p-1"
                                               {{ $isDayOff ? 'disabled' : '' }}>
                                    </td>
                                    <td class="p-2 text-center">
                                        <input type="checkbox" class="day-off-checkbox" data-index="{{ $index }}"
                                               {{ $isDayOff ? 'checked' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        // Маска телефона
        document.addEventListener('DOMContentLoaded', function() {
            var phoneInput = document.getElementById('store_phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                    e.target.value = '+7' + (x[2] ? '(' + x[2] + ')' : '') + (x[3] ? ' ' + x[3] : '') + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
                });
            }

            // Управление выходными днями в графике
            document.querySelectorAll('.day-off-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    var index = this.dataset.index;
                    var row = this.closest('tr');
                    var openInput = row.querySelector('input[name="working_hours[' + index + '][open]"]');
                    var closeInput = row.querySelector('input[name="working_hours[' + index + '][close]"]');
                    if (this.checked) {
                        openInput.value = '';
                        closeInput.value = '';
                        openInput.disabled = true;
                        closeInput.disabled = true;
                    } else {
                        openInput.disabled = false;
                        closeInput.disabled = false;
                    }
                });
            });
        });
    </script>
</x-app-layout>