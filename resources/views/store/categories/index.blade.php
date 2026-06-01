<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Управление категориями
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('store.categories.create') }}" class="bg-green-500 text-white px-4 py-2 rounded mb-6 inline-block">
                + Новая категория
            </a>

            @forelse ($rootCategories as $root)
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold">{{ $root->name }} <span class="text-sm text-gray-500">(родительская)</span></h3>
                        <div>
                            <a href="{{ route('store.categories.edit', $root) }}" class="text-blue-500 hover:underline">Ред.</a>
                            <form action="{{ route('store.categories.destroy', $root) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Удалить родительскую категорию и все подкатегории?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Удалить</button>
                            </form>
                        </div>
                    </div>
                    @if($root->children->isNotEmpty())
                        <table class="w-full text-sm border rounded">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-2 text-left">Подкатегория</th>
                                    <th class="p-2 text-left">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($root->children as $child)
                                    <tr class="border-t">
                                        <td class="p-2">{{ $child->name }}</td>
                                        <td class="p-2">
                                            <a href="{{ route('store.categories.edit', $child) }}" class="text-blue-500">Ред.</a>
                                            <form action="{{ route('store.categories.destroy', $child) }}" method="POST" class="inline ml-2">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500">Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500">Нет подкатегорий.</p>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('store.categories.create') }}?parent={{ $root->id }}" class="text-sm text-blue-500 hover:underline">
                            + Добавить подкатегорию для «{{ $root->name }}»
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Категорий пока нет. Создайте первую родительскую категорию.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>