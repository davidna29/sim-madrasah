<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tahun Ajaran dan Semester
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola periode akademik tanpa menghapus histori data lama.
                </p>
            </div>

            @can('permission', 'academic_years.create')
                <a
                    href="{{ route('admin.academic-years.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Tahun Ajaran
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Semester Aktif Sistem -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-gray-900">
                        Semester Aktif Sistem
                    </h3>

                    @if ($activeSemester)
                        <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <div class="text-sm text-gray-500">
                                    Tahun Ajaran
                                </div>

                                <div class="font-medium text-gray-900">
                                    {{ $activeSemester->academicYear?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-sm text-gray-500">
                                    Semester
                                </div>

                                <div class="font-medium text-gray-900">
                                    {{ $activeSemester->name }}
                                </div>
                            </div>

                            <div>
                                <div class="text-sm text-gray-500">
                                    Periode
                                </div>

                                <div class="font-medium text-gray-900">
                                    {{ $activeSemester->start_date->format('d M Y') }}
                                    sampai
                                    {{ $activeSemester->end_date->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500">
                            Belum ada semester aktif sistem. Aktifkan salah satu semester untuk menjadi acuan proses akademik.
                        </p>
                    @endif
                </div>
            </div>

            @forelse ($academicYears as $academicYear)
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $academicYear->name }}
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $academicYear->start_date->format('d M Y') }}
                                    sampai
                                    {{ $academicYear->end_date->format('d M Y') }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if ($academicYear->is_active)
                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        Tahun Ajaran Aktif
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Semester
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Periode
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
                                    @foreach ($academicYear->semesters as $semester)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900">
                                                    {{ $semester->name }}
                                                </div>

                                                <div class="text-xs text-gray-500">
                                                    {{ $semester->code }}
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 text-gray-700">
                                                {{ $semester->start_date->format('d M Y') }}
                                                sampai
                                                {{ $semester->end_date->format('d M Y') }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    @if ($semester->is_active)
                                                        <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                                            Tidak Aktif
                                                        </span>
                                                    @endif

                                                    @if ($semester->is_locked)
                                                        <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                            Terkunci
                                                        </span>
                                                    @else
                                                        <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700">
                                                            Terbuka
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    @can('permission', 'academic_years.activate')
                                                        @unless ($semester->is_active)
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.semesters.activate', $semester) }}"
                                                            >
                                                                @csrf
                                                                @method('PUT')

                                                                <button
                                                                    type="submit"
                                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                                >
                                                                    Jadikan Semester Aktif
                                                                </button>
                                                            </form>
                                                        @endunless
                                                    @endcan

                                                    @can('permission', 'academic_years.lock')
                                                        @unless ($semester->is_locked)
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.semesters.lock', $semester) }}"
                                                            >
                                                                @csrf
                                                                @method('PUT')

                                                                <button
                                                                    type="submit"
                                                                    class="text-sm font-medium text-red-700 hover:text-red-900"
                                                                >
                                                                    Kunci
                                                                </button>
                                                            </form>
                                                        @endunless
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-center text-sm text-gray-500">
                        Belum ada tahun ajaran.
                    </div>
                </div>
            @endforelse

            {{ $academicYears->links() }}
        </div>
    </div>
</x-app-layout>