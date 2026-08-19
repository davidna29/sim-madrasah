<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tambah Plotting Beban Mengajar
            </h2>
            <a href="{{ route('admin.teaching-assignments.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                ← Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg">

                {{-- Pesan error umum --}}
                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.teaching-assignments.store') }}">
                    @csrf

                    {{-- Tahun Ajaran --}}
                    <div class="mb-4">
                        <label for="academic_year_id" class="mb-1 block text-sm font-medium text-gray-700">
                            Tahun Ajaran <span class="text-red-500">*</span>
                        </label>
                        <select id="academic_year_id" name="academic_year_id"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('academic_year_id') border-red-500 @enderror">
                            <option value="">— Pilih Tahun Ajaran —</option>
                            @foreach ($academicYears as $ay)
                                <option value="{{ $ay->id }}"
                                    {{ old('academic_year_id', $selectedAcademicYearId) == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Semester --}}
                    <div class="mb-4">
                        <label for="semester_id" class="mb-1 block text-sm font-medium text-gray-700">
                            Semester <span class="text-red-500">*</span>
                        </label>
                        <select id="semester_id" name="semester_id"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('semester_id') border-red-500 @enderror">
                            <option value="">— Pilih Semester —</option>
                            @foreach ($semesters as $sem)
                                <option value="{{ $sem->id }}"
                                    data-academic-year="{{ $sem->academic_year_id }}"
                                    {{ old('semester_id', $selectedSemesterId) == $sem->id ? 'selected' : '' }}>
                                    {{ $sem->academicYear->name }} — {{ $sem->semester_type === 'odd' ? 'Ganjil' : 'Genap' }}
                                </option>
                            @endforeach
                        </select>
                        @error('semester_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rombongan Belajar --}}
                    <div class="mb-4">
                        <label for="class_group_id" class="mb-1 block text-sm font-medium text-gray-700">
                            Rombongan Belajar <span class="text-red-500">*</span>
                        </label>
                        <select id="class_group_id" name="class_group_id"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('class_group_id') border-red-500 @enderror">
                            <option value="">— Pilih Rombel —</option>
                            @foreach ($classGroups as $cg)
                                <option value="{{ $cg->id }}"
                                    {{ old('class_group_id') == $cg->id ? 'selected' : '' }}>
                                    {{ $cg->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_group_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mata Pelajaran --}}
                    <div class="mb-4">
                        <label for="subject_id" class="mb-1 block text-sm font-medium text-gray-700">
                            Mata Pelajaran <span class="text-red-500">*</span>
                        </label>
                        <select id="subject_id" name="subject_id"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('subject_id') border-red-500 @enderror">
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                    {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Guru --}}
                    <div class="mb-4">
                        <label for="teacher_user_id" class="mb-1 block text-sm font-medium text-gray-700">
                            Guru <span class="text-red-500">*</span>
                        </label>
                        <select id="teacher_user_id" name="teacher_user_id"
                                class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('teacher_user_id') border-red-500 @enderror">
                            <option value="">— Pilih Guru —</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                    {{ old('teacher_user_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Hanya menampilkan akun aktif dengan role Guru Mata Pelajaran, Wali Kelas, atau Guru BK.
                        </p>
                    </div>

                    {{-- Jam per Minggu --}}
                    <div class="mb-4">
                        <label for="weekly_hours" class="mb-1 block text-sm font-medium text-gray-700">
                            Jam per Minggu <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="weekly_hours" name="weekly_hours"
                               min="1" max="40"
                               value="{{ old('weekly_hours') }}"
                               class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('weekly_hours') border-red-500 @enderror"
                               placeholder="Contoh: 2">
                        @error('weekly_hours')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-6">
                        <label for="notes" class="mb-1 block text-sm font-medium text-gray-700">
                            Catatan <span class="text-gray-400">(opsional)</span>
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-500 @enderror"
                                  placeholder="Catatan tambahan jika ada...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol --}}
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('admin.teaching-assignments.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900">
                            Batal
                        </a>
                        <button type="submit"
                                class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Simpan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>