<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ruangan
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola ruang kelas, laboratorium, perpustakaan, dan ruangan lain.
                </p>
            </div>

            @can('permission', 'rooms.create')
                <a
                    href="{{ route('admin.rooms.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Ruangan
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kode
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Nama Ruangan
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Jenis
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kapasitas
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rooms as $room)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $room->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $room->name }}
                                        </div>

                                        @if ($room->location)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $room->location }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $room->room_type }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $room->capacity ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($room->is_active)
                                            <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @can('permission', 'rooms.update')
                                            <a
                                                href="{{ route('admin.rooms.edit', $room) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="6"
                                        class="px-4 py-6 text-center text-gray-500"
                                    >
                                        Belum ada ruangan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $rooms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>