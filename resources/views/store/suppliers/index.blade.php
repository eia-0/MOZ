<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Поставщики</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('store.suppliers.create') }}" class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block">+ Новый поставщик</a>
            <table class="w-full bg-white shadow rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr><th class="p-4">Название</th><th class="p-4">Телефон</th><th class="p-4">Действия</th></tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr class="border-t">
                            <td class="p-4">
                                <a href="{{ route('store.suppliers.show', $supplier) }}" class="text-blue-500 hover:underline">
                                    {{ $supplier->name }}
                                </a>
                            </td>
                            <td class="p-4">{{ $supplier->phone ?? '—' }}</td>
                            <td class="p-4">
                                <a href="{{ route('store.suppliers.edit', $supplier) }}" class="text-blue-500">Ред.</a>
                                <form action="{{ route('store.suppliers.destroy', $supplier) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Удалить?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500">Удал.</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>