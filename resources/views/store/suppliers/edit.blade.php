<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Редактировать поставщика: {{ $supplier->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('store.suppliers.update', $supplier) }}" method="POST" class="bg-white shadow rounded-lg p-6">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block font-medium">Название</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Телефон</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="w-full border rounded p-2" placeholder="+7 (___) ___-__-__">
                </div>

                <div class="mb-4">
                    <label class="block font-medium">Описание</label>
                    <textarea name="description" rows="3" class="w-full border rounded p-2">{{ old('description', $supplier->description) }}</textarea>
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Обновить</button>
            </form>
        </div>
    </div>
</x-app-layout>