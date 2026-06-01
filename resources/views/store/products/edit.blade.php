<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Редактировать товар: {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('store.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg p-6">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block">Название</label>
                    <input type="text" name="name" value="{{ $product->name }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block">Описание</label>
                    <textarea name="description" class="w-full border rounded p-2">{{ $product->description }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="block">Цена для покупателя</label>
                    <input type="number" name="price" step="0.01" value="{{ $product->price }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block">Цена поставщика</label>
                    <input type="number" name="supplier_price" step="0.01" value="{{ $product->supplier_price }}" class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block">Поставщик</label>
                    <select name="supplier_id" class="w-full border rounded p-2">
                        <option value="">-- Без поставщика --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $product->supplier_id == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block">Вес (г)</label>
                    <input type="number" name="weight" value="{{ $product->weight }}" class="w-full border rounded p-2" required>
                </div>

                {{-- Категории --}}
                @php
                    $currentCategory = $product->category;
                    $currentParent = $currentCategory ? $currentCategory->parent : null;
                    $currentParentId = $currentParent ? $currentParent->id : null;
                    $currentSubId = $currentCategory ? $currentCategory->id : null;
                @endphp
                <div class="mb-4">
                    <label class="block">Родительская категория</label>
                    <select id="parent-category" class="w-full border rounded p-2">
                        <option value="">-- Выберите категорию --</option>
                        @foreach ($rootCategories as $cat)
                            <option value="{{ $cat->id }}" {{ $currentParentId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block">Подкатегория</label>
                    <select name="category_id" id="subcategory" class="w-full border rounded p-2" required>
                        <option value="">-- Выберите подкатегорию --</option>
                        @if ($currentCategory && $currentCategory->parent)
                            @foreach ($currentCategory->parent->children as $child)
                                <option value="{{ $child->id }}" {{ $currentSubId == $child->id ? 'selected' : '' }}>
                                    {{ $child->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block">Изображение</label>
                    <input type="file" name="image" class="w-full">
                    @if ($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" class="w-32 mt-2">
                    @endif
                </div>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_available" value="1" @if($product->is_available) checked @endif class="rounded border-gray-300">
                        <span class="ml-2">Доступен</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label class="block">Текущий остаток (шт.)</label>
                    <input type="number" name="stock" min="0" value="{{ $product->stock }}" class="w-full border rounded p-2" placeholder="Оставьте пустым для неограниченного">
                </div>
                <div class="mb-4">
                    <label class="block">Начальный остаток (шт.)</label>
                    <input type="number" name="initial_stock" min="0" value="{{ $product->initial_stock }}" class="w-full border rounded p-2" placeholder="Для отображения прогресса">
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Обновить</button>
            </form>
        </div>
    </div>

    <script>
        const allCategories = @json($allCategories);
        const parentSelect = document.getElementById('parent-category');
        const subSelect = document.getElementById('subcategory');

        function loadSubcategories(parentId, selectedSubId = null) {
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
                const selected = (selectedSubId && child.id == selectedSubId) ? 'selected' : '';
                html += `<option value="${child.id}" ${selected}>${child.name}</option>`;
            });
            subSelect.innerHTML = html;
        }

        parentSelect.addEventListener('change', function() {
            loadSubcategories(this.value);
        });

        document.addEventListener('DOMContentLoaded', function() {
            @if ($currentParentId)
                loadSubcategories('{{ $currentParentId }}', '{{ $currentSubId }}');
            @endif
        });
    </script>
</x-app-layout>