<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Data Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data master siswa tanpa mengubah histori kelas.
                </p>
            </div>

            @can('permission', 'students.create')
                <a
                    href="{{ route('admin.students.create') }}"
                    class="whitespace-nowrap rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                    >
                    Tambah Siswa
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

            <!-- Filter siswa -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <form
                        method="GET"
                        action="{{ route('admin.students.index') }}"
                        class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-8"
                    >
                        <div class="md:col-span-2">
                            <x-input-label for="q" value="Cari Siswa" />

                            <x-text-input
                                id="q"
                                name="q"
                                type="text"
                                class="mt-1 block w-full"
                                :value="$search"
                                placeholder="Nama, NIS, NISN, atau nomor registrasi"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="class_group_id" value="Filter Rombongan Belajar" />

                            <select
                                id="class_group_id"
                                name="class_group_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            >
                                <option value="">Semua Rombongan Belajar</option>

                                @foreach ($classGroups as $classGroup)
                                    <option
                                        value="{{ $classGroup->id }}"
                                        @selected((string) $selectedClassGroupId === (string) $classGroup->id)
                                    >
                                        {{ $classGroup->academicYear?->name }}
                                        -
                                        {{ $classGroup->name }}
                                        -
                                        {{ $classGroup->gradeLevel?->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div class="md:col-span-2">
                            <x-input-label for="status" value="Filter Status" />

                            <select
                                id="status"
                                name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            >
                                <option value="">Semua Status</option>

                                @foreach ($studentStatuses as $statusValue => $statusLabel)
                                    <option
                                        value="{{ $statusValue }}"
                                        @selected($selectedStatus === $statusValue)
                                    >
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filter Tahun Masuk -->
                        <div class="md:col-span-2">
                            <x-input-label for="admission_academic_year_id" value="Tahun Masuk" />

                            <select
                                id="admission_academic_year_id"
                                name="admission_academic_year_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            >
                                <option value="">Semua Tahun Masuk</option>

                                @foreach ($admissionAcademicYears as $academicYear)
                                    <option
                                        value="{{ $academicYear->id }}"
                                        @selected((string) $selectedAdmissionAcademicYearId === (string) $academicYear->id)
                                    >
                                        {{ $academicYear->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                       <div class="flex flex-wrap items-end gap-2 md:col-span-2">
                            <button
                                type="submit"
                                class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                            >
                                Terapkan
                            </button>

                            <a
                                href="{{ route('admin.students.index') }}"
                                class="whitespace-nowrap rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Reset
                            </a>
                            <!-- tombol export -->
                            <a
                                href="{{ route('admin.students.export', request()->query()) }}"
                                class="whitespace-nowrap rounded-md border border-green-700 bg-white px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-50"
                            >
                                Export CSV
                            </a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">NIS</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">NISN</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tahun Masuk</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kelas Saat Ini</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Akun</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $student->person?->full_name }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $student->registration_number ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $student->student_number ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $student->nisn ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $student->admissionAcademicYear?->name ?? '-' }}
                                    </td>

                                    <!-- Kelas Saat Ini -->
                                    <td class="px-4 py-3">
                                        @if ($student->currentClassHistory)
                                            <div class="font-medium text-gray-900">
                                                {{ $student->currentClassHistory->classGroup?->name ?? '-' }}
                                            </div>

                                            <div class="text-xs text-gray-500">
                                                {{ $student->currentClassHistory->semester?->name ?? '-' }}
                                            </div>
                                        @else
                                            <span class="text-gray-500">
                                                Belum ada kelas aktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>{{ $student->person?->email ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $student->person?->phone ?? '-' }}
                                        </div>
                                    </td>

                                    <!-- Update View Data Siswa -->
                                    <td class="px-4 py-3">
                                        @if ($student->person?->user)
                                            <div class="font-mono text-xs text-gray-900">
                                                {{ $student->person->user->username }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $student->person->user->status }}
                                            </div>
                                        @else
                                            @can('permission', 'students.account.create')
                                                <a
                                                    href="{{ route('admin.students.accounts.create', $student) }}"
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

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ $student->status }}
                                            </span>

                                            @if ($student->is_active)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- <td class="px-4 py-3">
                                        @can('permission', 'students.update')
                                            <a
                                                href="{{ route('admin.students.edit', $student) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td> -->
                                    <!-- Update View Daftar Siswa -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            @can('permission', 'students.update')
                                                <a
                                                    href="{{ route('admin.students.edit', $student) }}"
                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                >
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('permission', 'student_class_histories.view')
                                                <a
                                                    href="{{ route('admin.students.class-histories.index', $student) }}"
                                                    class="text-sm font-medium text-blue-700 hover:text-blue-900"
                                                >
                                                    Riwayat Kelas
                                                </a>
                                            @endcan

                                            <!-- Wali -->
                                            @can('permission', 'student_guardians.view')
                                                <a
                                                    href="{{ route('admin.students.guardians.index', $student) }}"
                                                    class="text-sm font-medium text-purple-700 hover:text-purple-900"
                                                >
                                                    Wali
                                                </a>
                                            @endcan
                                            <!-- Tambahan Link Portofolio -->
                                            @can('permission', 'student_portfolios.view')
                                                <a
                                                    href="{{ route('admin.students.portfolio.show', $student) }}"
                                                    class="text-sm font-medium text-indigo-700 hover:text-indigo-900"
                                                >
                                                    Portofolio
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada data siswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
