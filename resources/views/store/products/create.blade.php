<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Добавить товар
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('store.products.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
                @csrf
                <div class="mb-4">
                    <label class="block">Название</label>
                    <input type="text" name="name" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block">Описание</label>
                    <textarea name="description" class="w-full border rounded p-2"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block">Цена для покупателя</label>
                    <input type="number" name="price" step="0.01" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block">Цена поставщика <span class="text-gray-400">(сколько получает поставщик за ед.)</span></label>
                    <input type="number" name="supplier_price" step="0.01" class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block">Поставщик</label>
                    <select name="supplier_id" class="w-full border rounded p-2">
                        <option value="">-- Без поставщика --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block">Вес (г)</label>
                    <input type="number" name="weight" class="w-full border rounded p-2" required>
                </div>

                {{-- Категории --}}
                <div class="mb-4">
                    <label class="block">Родительская категория</label>
                    <select id="parent-category" class="w-full border rounded p-2">
                        <option value="">-- Выберите категорию --</option>
                        @foreach ($rootCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block">Подкатегория</label>
                    <select name="category_id" id="subcategory" class="w-full border rounded p-2" required>
                        <option value="">-- Сначала выберите родительскую --</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block">Изображение</label>
                    <input type="file" name="image" class="w-full">
                </div>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_available" value="1" checked class="rounded border-gray-300">
                        <span class="ml-2">Доступен</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label class="block">Текущий остаток (шт.) <span class="text-gray-400">(оставьте пустым, если неограничено)</span></label>
                    <input type="number" name="stock" min="0" class="w-full border rounded p-2" placeholder="Например, 10">
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        const allCategories = @json($allCategories);
        const parentSelect = document.getElementById('parent-category');
        const subSelect = document.getElementById('subcategory');

        parentSelect.addEventListener('change', function() {
            const parentId = this.value;
            subSelect.innerHTML = '';
            if (!parentId) {
                subSelect.innerHTML = '<option value="">-- Сначала выберите родительскую --</option>';
                subSelect.disabled = true;
                return;
            }
            subSelect.disabled = false;
            const children = allCategories.filter(cat => cat.parent_id == parentId);
            if (children.length === 0) {
                subSelect.innerHTML = '<option value="">-- Нет подкатегорий --</option>';
                return;
            }
            let html = '<option value="">-- Выберите подкатегорию --</option>';
            children.forEach(child => {
                html += `<option value="${child.id}">${child.name}</option>`;
            });
            subSelect.innerHTML = html;
        });
    </script>
</x-app-layout>