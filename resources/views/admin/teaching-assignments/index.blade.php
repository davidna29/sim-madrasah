<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Plotting Beban Mengajar
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar guru, mata pelajaran, rombel, dan jumlah jam per minggu.
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

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <form
                        method="GET"
                        action="{{ route('admin.teaching-assignments.index') }}"
                        class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4"
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

                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                            >
                                Terapkan
                            </button>

                            <a
                                href="{{ route('admin.teaching-assignments.index') }}"
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
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Tahun Ajaran
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Semester
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Rombel
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Mata Pelajaran
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Guru
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Jam/Minggu
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @forelse ($teachingAssignments as $teachingAssignment)
                                    <tr>
                                        <td class="px-4 py-3">
                                            {{ $teachingAssignment->academicYear?->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $teachingAssignment->semester?->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">
                                                {{ $teachingAssignment->classGroup?->name ?? '-' }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $teachingAssignment->classGroup?->gradeLevel?->name ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $teachingAssignment->subject?->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $teachingAssignment->teacher?->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $teachingAssignment->weekly_hours }}
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($teachingAssignment->is_active)
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
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            Belum ada data plotting beban mengajar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-6">
                            {{ $teachingAssignments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>