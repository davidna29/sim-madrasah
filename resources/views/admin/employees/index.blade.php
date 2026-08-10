<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Guru dan Pegawai
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data guru dan tenaga kependidikan madrasah.
                </p>
            </div>

            @can('permission', 'employees.create')
                <a
                    href="{{ route('admin.employees.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Guru/Pegawai
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nomor Pegawai</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Jenis</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Jabatan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Akun</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $employee->person?->full_name }}
                                        </div>

                                        @if ($employee->is_teacher)
                                            <div class="mt-1 text-xs text-green-700">
                                                Guru
                                            </div>
                                        @else
                                            <div class="mt-1 text-xs text-gray-500">
                                                Tenaga Kependidikan
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $employee->employee_number ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $employee->employee_type }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $employee->position ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>{{ $employee->person?->email ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $employee->person?->phone ?? '-' }}
                                        </div>
                                    </td>

                                    <!-- Update View Index Pegawai -->
                                    <td class="px-4 py-3">
                                        @if ($employee->person?->user)
                                            <div class="font-mono text-xs text-gray-900">
                                                {{ $employee->person->user->username }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $employee->person->user->status }}
                                            </div>
                                        @else
                                            @can('permission', 'employees.account.create')
                                                <a
                                                    href="{{ route('admin.employees.accounts.create', $employee) }}"
                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                >
                                                    Buat Akun
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-500">
                                                    Belum ada akun
                                                </span>
                                            @endcan
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        @if ($employee->is_active)
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
                                        @can('permission', 'employees.update')
                                            <a
                                                href="{{ route('admin.employees.edit', $employee) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada data guru atau pegawai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
