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

        <!-- Update View Index Rombongan Belajar - form filter -->
        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
            <div class="p-6">
                <form
                    method="GET"
                    action="{{ route('admin.class-groups.index') }}"
                    class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4"
                >
                    <div class="md:col-span-2">
                        <x-input-label for="academic_year_id" value="Filter Tahun Ajaran" />

                        <select
                            id="academic_year_id"
                            name="academic_year_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        >
                            <option value="">Semua Tahun Ajaran</option>

                            @foreach ($academicYears as $academicYear)
                                <option
                                    value="{{ $academicYear->id }}"
                                    @selected((string) $selectedAcademicYearId === (string) $academicYear->id)
                                >
                                    {{ $academicYear->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                        >
                            Terapkan
                        </button>

                        <a
                            href="{{ route('admin.class-groups.index') }}"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Reset
                        </a>
                    </div>
                </form>

                <div class="overflow-x-auto">
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

                                            <!-- Tombol Export Excel -->
                                            @can('permission', 'class_groups.export_students')
                                                <a
                                                    href="{{ route('admin.class-groups.students.excel', $classGroup) }}"
                                                    class="text-sm font-medium text-emerald-700 hover:text-emerald-900"
                                                >
                                                    Export Excel
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
