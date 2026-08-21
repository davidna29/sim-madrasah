<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tambah Ketersediaan Guru
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Catat waktu ketika guru tidak tersedia untuk mengajar.
                </p>
            </div>

            <a href="{{ route('admin.teacher-availabilities.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                ← Kembali ke Ketersediaan Guru
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                Untuk tahap ini, tipe ketersediaan yang tersedia baru "Tidak tersedia".
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.teacher-availabilities.store') }}" class="space-y-6">
                        @csrf

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
                                        @selected((string) old('academic_year_id', $selectedAcademicYearId) === (string) $academicYear->id)
                                    >
                                        {{ $academicYear->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('academic_year_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                                        @selected((string) old('semester_id', $selectedSemesterId) === (string) $semester->id)
                                    >
                                        {{ $semester->name }} - {{ $semester->academicYear?->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('semester_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="teacher_user_id" value="Guru" />

                            <select
                                id="teacher_user_id"
                                name="teacher_user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                required
                            >
                                <option value="">Pilih Guru</option>

                                @foreach ($teachers as $teacher)
                                    <option
                                        value="{{ $teacher->id }}"
                                        @selected((string) old('teacher_user_id', $selectedTeacherUserId) === (string) $teacher->id)
                                    >
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('teacher_user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="day_of_week" value="Hari" />

                            <select
                                id="day_of_week"
                                name="day_of_week"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                required
                            >
                                <option value="">Pilih Hari</option>

                                @foreach ([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old('day_of_week') === (string) $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('day_of_week')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-input-label for="starts_at" value="Jam Mulai" />

                                <input
                                    id="starts_at"
                                    name="starts_at"
                                    type="time"
                                    value="{{ old('starts_at') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    required
                                >

                                @error('starts_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-input-label for="ends_at" value="Jam Selesai" />

                                <input
                                    id="ends_at"
                                    name="ends_at"
                                    type="time"
                                    value="{{ old('ends_at') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    required
                                >

                                @error('ends_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <x-input-label for="availability_type" value="Tipe Ketersediaan" />

                            <select
                                id="availability_type"
                                name="availability_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                required
                            >
                                <option value="unavailable" @selected(old('availability_type', 'unavailable') === 'unavailable')>
                                    Tidak tersedia
                                </option>
                            </select>

                            @error('availability_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="reason" value="Alasan Singkat" />

                            <input
                                id="reason"
                                name="reason"
                                type="text"
                                value="{{ old('reason') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                maxlength="150"
                                placeholder="Contoh: Tugas luar"
                            >

                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input-label for="notes" value="Catatan Tambahan" />

                            <textarea
                                id="notes"
                                name="notes"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                placeholder="Catatan opsional."
                            >{{ old('notes') }}</textarea>

                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a
                                href="{{ route('admin.teacher-availabilities.index') }}"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                            >
                                Simpan Ketersediaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>