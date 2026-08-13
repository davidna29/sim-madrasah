<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Bulk Assignment Siswa ke Rombel
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Pilih banyak siswa, lalu tempatkan ke rombongan belajar pada semester tertentu.
                </p>
            </div>

            <a
                href="{{ route('admin.students.index') }}"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <div class="font-medium">
                        Data belum bisa disimpan.
                    </div>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('admin.students.bulk-class-assignment.store') }}"
                class="space-y-6"
            >
                @csrf

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <x-input-label for="academic_year_id" value="Tahun Ajaran" />

                            <select
                                id="academic_year_id"
                                name="academic_year_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                required
                            >
                                <option value="">Pilih Tahun Ajaran</option>

                                @foreach ($academicYears as $academicYear)
                                    <option
                                        value="{{ $academicYear->id }}"
                                        @selected((string) old('academic_year_id') === (string) $academicYear->id)
                                    >
                                        {{ $academicYear->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="semester_id" value="Semester" />

                            <select
                                id="semester_id"
                                name="semester_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                required
                            >
                                <option value="">Pilih Semester</option>

                                @foreach ($semesters as $semester)
                                    <option
                                        value="{{ $semester->id }}"
                                        @selected((string) old('semester_id') === (string) $semester->id)
                                    >
                                        {{ $semester->academicYear?->name }}
                                        -
                                        {{ $semester->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="class_group_id" value="Rombongan Belajar" />

                            <select
                                id="class_group_id"
                                name="class_group_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                required
                            >
                                <option value="">Pilih Rombel</option>

                                @foreach ($classGroups as $classGroup)
                                    <option
                                        value="{{ $classGroup->id }}"
                                        @selected((string) old('class_group_id') === (string) $classGroup->id)
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

                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai" />

                            <x-text-input
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="mt-1 block w-full"
                                :value="old('start_date', now()->toDateString())"
                                required
                            />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="notes" value="Catatan" />

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            placeholder="Opsional"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Pilih Siswa
                            </h3>

                            <p class="text-sm text-gray-500">
                                Centang siswa yang akan dimasukkan ke rombel terpilih.
                            </p>
                        </div>

                        <div class="text-sm text-gray-500">
                            Total siswa aktif: {{ $students->count() }}
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Pilih
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Nama
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        NIS
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Tahun Masuk
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Kelas Saat Ini
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($students as $student)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input
                                                type="checkbox"
                                                name="student_ids[]"
                                                value="{{ $student->id }}"
                                                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                                                @checked(in_array((string) $student->id, old('student_ids', []), true))
                                            />
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">
                                                {{ $student->person?->full_name ?? '-' }}
                                            </div>

                                            <div class="text-xs text-gray-500">
                                                {{ $student->registration_number ?? '-' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $student->student_number ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $student->admissionAcademicYear?->name ?? '-' }}
                                        </td>

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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                            Belum ada siswa aktif.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-2">
                        <button
                            type="submit"
                            class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                        >
                            Simpan Assignment
                        </button>

                        <a
                            href="{{ route('admin.students.index') }}"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
