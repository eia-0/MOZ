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
                    <label class="block">Цена</label>
                    <input type="number" name="price" step="0.01" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block">Вес (г)</label>
                    <input type="number" name="weight" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block">Категория</label>
                    <select name="category_id" class="w-full border rounded p-2">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
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
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Сохранить</button>
            </form>
        </div>
    </div>
</x-app-layout>