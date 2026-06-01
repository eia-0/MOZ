<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Редактировать категорию: {{ $category->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('store.categories.update', $category) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf @method('PUT')

                <div class="mb-4">
                    <label class="block font-medium">Название</label>
                    <input type="text" name="name" value="{{ $category->name }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Slug</label>
                    <input type="text" name="slug" value="{{ $category->slug }}" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium">Родительская категория</label>
                    <select name="parent_id" class="w-full border rounded p-2">
                        <option value="">-- Нет (корневая) --</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Обновить</button>
            </form>
        </div>
    </div>
</x-app-layout>