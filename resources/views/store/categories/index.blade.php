<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Категории
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('store.categories.create') }}" class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block">+ Новая категория</a>

            <table class="w-full bg-white shadow rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left">ID</th>
                        <th class="p-4 text-left">Название</th>
                        <th class="p-4 text-left">Slug</th>
                        <th class="p-4 text-left">Родительская</th>
                        <th class="p-4 text-left">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-t">
                            <td class="p-4">{{ $category->id }}</td>
                            <td class="p-4">{{ $category->name }}</td>
                            <td class="p-4">{{ $category->slug }}</td>
                            <td class="p-4">{{ $category->parent->name ?? '—' }}</td>
                            <td class="p-4">
                                <a href="{{ route('store.categories.edit', $category) }}" class="text-blue-500">Ред.</a>
                                <form action="{{ route('store.categories.destroy', $category) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Удалить категорию?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500">Удал.</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">Нет категорий.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-app-layout>