<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ketersediaan Guru
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar waktu ketika guru tidak tersedia atau memiliki aturan ketersediaan tertentu.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                Tahap ini baru menampilkan daftar ketersediaan guru. Form tambah/edit akan dibuat pada tahap berikutnya.
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <form
                        method="GET"
                        action="{{ route('admin.teacher-availabilities.index') }}"
                        class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-5"
                    >
                        <div>
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

                        <div>
                            <x-input-label for="semester_id" value="Filter Semester" />

                            <select
                                id="semester_id"
                                name="semester_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            >
                                <option value="">Semua Semester</option>

                                @foreach ($semesters as $semester)
                                    <option
                                        value="{{ $semester->id }}"
                                        @selected((string) $selectedSemesterId === (string) $semester->id)
                                    >
                                        {{ $semester->name }} - {{ $semester->academicYear?->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="teacher_user_id" value="Filter Guru" />

                            <select
                                id="teacher_user_id"
                                name="teacher_user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            >
                                <option value="">Semua Guru</option>

                                @foreach ($teachers as $teacher)
                                    <option
                                        value="{{ $teacher->id }}"
                                        @selected((string) $selectedTeacherUserId === (string) $teacher->id)
                                    >
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end gap-2 md:col-span-2">
                            <button
                                type="submit"
                                class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                            >
                                Terapkan
                            </button>

                            <a
                                href="{{ route('admin.teacher-availabilities.index') }}"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Reset
                            </a>
                        </div>
                    </form>

                    @php
                        $dayLabels = [
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu',
                        ];

                        $availabilityTypeLabels = [
                            'unavailable' => 'Tidak tersedia',
                        ];
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Tahun Ajaran
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Semester
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Guru
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Hari
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Jam
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Tipe
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Alasan
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @forelse ($teacherAvailabilities as $teacherAvailability)
                                    <tr>
                                        <td class="px-4 py-3">
                                            {{ $teacherAvailability->academicYear?->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $teacherAvailability->semester?->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $teacherAvailability->teacher?->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $dayLabels[$teacherAvailability->day_of_week] ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ substr((string) $teacherAvailability->starts_at, 0, 5) }}
                                            -
                                            {{ substr((string) $teacherAvailability->ends_at, 0, 5) }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $availabilityTypeLabels[$teacherAvailability->availability_type] ?? $teacherAvailability->availability_type }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $teacherAvailability->reason ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($teacherAvailability->is_active)
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
                                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                            Belum ada data ketersediaan guru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-6">
                            {{ $teacherAvailabilities->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>