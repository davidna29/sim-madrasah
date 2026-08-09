<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Permission') }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar izin rinci yang digunakan untuk membatasi akses fitur.
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
                                        Permission
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Module
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Action
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Label
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Role
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($permissions as $permission)
                                    <tr>
                                        <td class="px-4 py-3 font-mono text-gray-900">
                                            {{ $permission->name }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $permission->module }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $permission->action }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            <div class="font-medium">
                                                {{ $permission->display_name }}
                                            </div>

                                            @if ($permission->description)
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ $permission->description }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $permission->roles_count }}
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($permission->is_active)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                            Belum ada data permission.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>