<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Role') }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar kelompok hak akses pengguna SIM Madrasah.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Nama Role
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Label
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Permission
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        User
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Sistem
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($roles as $role)
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-gray-900">
                                            {{ $role->name }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            <div class="font-medium">
                                                {{ $role->display_name }}
                                            </div>

                                            @if ($role->description)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $role->description }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $role->permissions_count }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $role->users_count }}
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($role->is_system)
                                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                                    Ya
                                                </span>
                                            @else
                                                <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                                    Tidak
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($role->is_active)
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
                                            <a href="{{ route('admin.roles.show', $role) }}" class="text-sm font-medium text-green-700 hover:text-green-900">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            Belum ada data role.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>