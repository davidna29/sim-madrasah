<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rombongan Belajar
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola kelas aktif berdasarkan tahun ajaran.
                </p>
            </div>

            @can('permission', 'class_groups.create')
                <a
                    href="{{ route('admin.class-groups.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Rombel
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kode</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Rombel</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tahun Ajaran</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tingkat</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Ruangan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Wali Kelas</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($classGroups as $classGroup)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $classGroup->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $classGroup->name }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            Kapasitas: {{ $classGroup->capacity ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->academicYear?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->gradeLevel?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->room?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->homeroomTeacher?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($classGroup->is_active)
                                            <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Tombol Cetak di Halaman Rombongan Belajar -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            @can('permission', 'class_groups.update')
                                                <a
                                                    href="{{ route('admin.class-groups.edit', $classGroup) }}"
                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                >
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('permission', 'class_groups.print_students')
                                                <a
                                                    href="{{ route('admin.class-groups.students.pdf', $classGroup) }}"
                                                    target="_blank"
                                                    class="text-sm font-medium text-blue-700 hover:text-blue-900"
                                                >
                                                    Cetak Siswa
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada rombongan belajar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $classGroups->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
