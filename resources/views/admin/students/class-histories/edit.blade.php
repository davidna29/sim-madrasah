<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Riwayat Kelas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $student->person?->full_name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('admin.students.class-histories.update', [$student, $classHistory]) }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    @if ($activeSemester)
                        <div class="rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">
                            Semester aktif sistem:
                            <span class="font-medium">
                                {{ $activeSemester->academicYear?->name ?? '-' }} - {{ $activeSemester->name }}
                            </span>
                        </div>
                    @endif

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
                                    @selected((string) old('academic_year_id', $classHistory->academic_year_id) === (string) $academicYear->id)
                                >
                                    {{ $academicYear->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
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
                                    @selected((string) old('semester_id', $classHistory->semester_id) === (string) $semester->id)
                                >
                                    {{ $semester->academicYear?->name }}
                                    -
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('semester_id')" class="mt-2" />
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
                                    @selected((string) old('class_group_id', $classHistory->class_group_id) === (string) $classGroup->id)
                                >
                                    {{ $classGroup->academicYear?->name }}
                                    -
                                    {{ $classGroup->name }}
                                    -
                                    {{ $classGroup->gradeLevel?->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('class_group_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status Penempatan" />

                        <select
                            id="status"
                            name="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            @foreach ([
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'transferred' => 'Pindah',
                                'graduated' => 'Lulus',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('status', $classHistory->status) === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai" />

                            <x-text-input
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="mt-1 block w-full"
                                :value="old('start_date', optional($classHistory->start_date)->format('Y-m-d'))"
                            />

                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="end_date" value="Tanggal Selesai" />

                            <x-text-input
                                id="end_date"
                                name="end_date"
                                type="date"
                                class="mt-1 block w-full"
                                :value="old('end_date', optional($classHistory->end_date)->format('Y-m-d'))"
                            />

                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Catatan" />

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        >{{ old('notes', $classHistory->notes) }}</textarea>

                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="is_current"
                            name="is_current"
                            type="checkbox"
                            value="1"
                            class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                            @checked(old('is_current', $classHistory->is_current))
                        >

                        <label for="is_current" class="text-sm text-gray-700">
                            Jadikan kelas aktif saat ini
                        </label>
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <a
                        href="{{ route('admin.students.class-histories.index', $student) }}"
                        class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
