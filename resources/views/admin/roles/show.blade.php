<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Role
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Informasi role dan permission yang dimiliki.
                </p>
            </div>

            <a
                href="{{ route('admin.roles.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
            >
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                        <div>
                            <div class="text-sm text-gray-500">
                                Nama Role
                            </div>

                            <div class="mt-1 font-mono text-gray-900">
                                {{ $role->name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">
                                Label
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $role->display_name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">
                                Jumlah Permission
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $role->permissions_count }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">
                                Jumlah User
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $role->users_count }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <div class="text-sm text-gray-500">
                                Role Sistem
                            </div>

                            <div class="mt-2">
                                @if ($role->is_system)
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                        Ya
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                        Tidak
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">
                                Status
                            </div>

                            <div class="mt-2">
                                @if ($role->is_active)
                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500">
                                Dibuat
                            </div>

                            <div class="mt-1 text-gray-900">
                                {{ $role->created_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    @if ($role->description)
                        <div class="mt-6">
                            <div class="text-sm text-gray-500">
                                Deskripsi
                            </div>

                            <p class="mt-1 text-gray-700">
                                {{ $role->description }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Permission Role
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Daftar permission yang dimiliki oleh role ini, dikelompokkan berdasarkan module.
                            </p>
                        </div>
                    </div>

                    @if ($permissionsByModule->isEmpty())
                        <div class="rounded-md border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
                            Role ini belum memiliki permission.
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach ($permissionsByModule as $module => $permissions)
                                <div class="rounded-lg border border-gray-200">
                                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                                        <h4 class="font-semibold text-gray-800">
                                            Module: {{ $module }}
                                        </h4>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                                            <thead class="bg-white">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                                        Permission
                                                    </th>
                                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                                        Action
                                                    </th>
                                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                                        Label
                                                    </th>
                                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                                        Status
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-gray-100 bg-white">
                                                @foreach ($permissions as $permission)
                                                    <tr>
                                                        <td class="px-4 py-3 font-mono text-gray-900">
                                                            {{ $permission->name }}
                                                        </td>

                                                        <td class="px-4 py-3 text-gray-700">
                                                            {{ $permission->action }}
                                                        </td>

                                                        <td class="px-4 py-3 text-gray-700">
                                                            {{ $permission->display_name }}
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
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
