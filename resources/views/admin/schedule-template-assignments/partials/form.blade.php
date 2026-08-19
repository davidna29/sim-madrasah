<form
    method="POST"
    action="{{ $action }}"
    class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
>
    @csrf

    <div class="p-6 space-y-6">
        <div class="rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-green-800">
            Pilih tahun ajaran, semester, rombel, dan template jadwal.
            Jika rombel sudah punya template pada semester yang sama, sistem akan menolak kecuali opsi replace dicentang.
        </div>

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
                        @selected((string) old('academic_year_id', $assignment->academic_year_id) === (string) $academicYear->id)
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
                        @selected((string) old('semester_id', $assignment->semester_id) === (string) $semester->id)
                    >
                        {{ $semester->name }} - {{ $semester->academicYear?->name }}
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
                        @selected((string) old('class_group_id', $assignment->class_group_id) === (string) $classGroup->id)
                    >
                        {{ $classGroup->name }} - {{ $classGroup->academicYear?->name }} - {{ $classGroup->gradeLevel?->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('class_group_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="schedule_template_id" value="Template Jadwal" />

            <select
                id="schedule_template_id"
                name="schedule_template_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                <option value="">Pilih Template Jadwal</option>

                @foreach ($scheduleTemplates as $scheduleTemplate)
                    <option
                        value="{{ $scheduleTemplate->id }}"
                        @selected((string) old('schedule_template_id', $assignment->schedule_template_id) === (string) $scheduleTemplate->id)
                    >
                        {{ $scheduleTemplate->name }} - {{ $scheduleTemplate->code }} - {{ $scheduleTemplate->slots_count }} slot
                    </option>
                @endforeach
            </select>

            <p class="mt-1 text-xs text-gray-500">
                Template harus aktif dan sudah memiliki slot sebelum bisa dipakai rombel.
            </p>

            <x-input-error :messages="$errors->get('schedule_template_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="notes" value="Catatan" />

            <textarea
                id="notes"
                name="notes"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                placeholder="Opsional. Contoh: template khusus kelas tahfidz."
            >{{ old('notes', $assignment->notes) }}</textarea>

            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
        </div>

        <div class="rounded-lg border border-yellow-100 bg-yellow-50 p-4">
            <label class="flex items-start gap-3 text-sm text-yellow-900">
                <input
                    name="replace_existing"
                    type="checkbox"
                    value="1"
                    class="mt-1 rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                    @checked(old('replace_existing'))
                >

                <span>
                    Replace assignment lama jika rombel sudah memiliki template pada semester yang sama.
                    Gunakan hanya jika admin memang ingin mengganti template jadwal rombel tersebut.
                </span>
            </label>

            <x-input-error :messages="$errors->get('replace_existing')" class="mt-2" />
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.schedule-template-assignments.index') }}"
            class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            Batal
        </a>

        <button
            type="submit"
            class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
        >
            {{ $buttonLabel }}
        </button>
    </div>
</form>