<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Новая категория
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('store.categories.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf
                <div class="mb-4">
                    <label class="block font-medium">Название категории</label>
                    <input type="text" name="name" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Тип категории</label>
                    <div class="mt-2 space-y-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="parent_id" value="" checked class="form-radio">
                            <span class="ml-2">Родительская категория (главный раздел)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="parent_id" value="{{ old('parent_id', request('parent')) }}" class="form-radio">
                            <span class="ml-2">Подкатегория (выберите родителя)</span>
                        </label>
                    </div>
                    @if($rootCategories->isNotEmpty())
                        <div class="mt-2" id="parent-select-block" style="{{ request('parent') ? 'display:block' : 'display:none' }}">
                            <select name="parent_id" class="w-full border rounded p-2">
                                <option value="">— Выберите родительскую категорию —</option>
                                @foreach ($rootCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('parent') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Создать</button>
            </form>
        </div>
    </div>

    <script>
        // Показывать или скрывать выбор родителя при переключении радио
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="parent_id"]');
            const selectBlock = document.getElementById('parent-select-block');
            const select = selectBlock?.querySelector('select');

            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === '') {
                        selectBlock.style.display = 'none';
                        if (select) select.value = '';
                    } else {
                        selectBlock.style.display = 'block';
                        // Если значение не пустое (например, из URL parent), подставим
                        if (this.value) select.value = this.value;
                    }
                });
            });

            // Инициализация при загрузке
            const checkedRadio = document.querySelector('input[name="parent_id"]:checked');
            if (checkedRadio && checkedRadio.value !== '') {
                selectBlock.style.display = 'block';
            }
        });
    </script>
</x-app-layout>