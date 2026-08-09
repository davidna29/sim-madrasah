<form
    method="POST"
    action="{{ $action }}"
    class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="p-6 space-y-6">
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
                        @selected((string) old('academic_year_id', $classGroup->academic_year_id) === (string) $academicYear->id)
                    >
                        {{ $academicYear->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="grade_level_id" value="Tingkat Kelas" />

            <select
                id="grade_level_id"
                name="grade_level_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                <option value="">Pilih Tingkat Kelas</option>

                @foreach ($gradeLevels as $gradeLevel)
                    <option
                        value="{{ $gradeLevel->id }}"
                        @selected((string) old('grade_level_id', $classGroup->grade_level_id) === (string) $gradeLevel->id)
                    >
                        {{ $gradeLevel->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('grade_level_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="room_id" value="Ruangan" />

            <select
                id="room_id"
                name="room_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >
                <option value="">Tanpa Ruangan</option>

                @foreach ($rooms as $room)
                    <option
                        value="{{ $room->id }}"
                        @selected((string) old('room_id', $classGroup->room_id) === (string) $room->id)
                    >
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="homeroom_teacher_user_id" value="Wali Kelas" />

            <select
                id="homeroom_teacher_user_id"
                name="homeroom_teacher_user_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >
                <option value="">Belum ditentukan</option>

                @foreach ($homeroomTeachers as $teacher)
                    <option
                        value="{{ $teacher->id }}"
                        @selected((string) old('homeroom_teacher_user_id', $classGroup->homeroom_teacher_user_id) === (string) $teacher->id)
                    >
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('homeroom_teacher_user_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="code" value="Kode Rombel" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $classGroup->code)"
                placeholder="VII-A"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Rombel" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $classGroup->name)"
                placeholder="Kelas VII A"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="parallel_name" value="Nama Paralel" />

            <x-text-input
                id="parallel_name"
                name="parallel_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('parallel_name', $classGroup->parallel_name)"
                placeholder="A"
            />

            <x-input-error :messages="$errors->get('parallel_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="capacity" value="Kapasitas" />

            <x-text-input
                id="capacity"
                name="capacity"
                type="number"
                min="1"
                class="mt-1 block w-full"
                :value="old('capacity', $classGroup->capacity)"
            />

            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="status" value="Status" />

            <select
                id="status"
                name="status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ([
                    'active' => 'Aktif',
                    'inactive' => 'Nonaktif',
                    'archived' => 'Arsip',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('status', $classGroup->status ?: 'active') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                @checked(old('is_active', $classGroup->is_active ?? true))
            >

            <label for="is_active" class="text-sm text-gray-700">
                Aktif
            </label>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.class-groups.index') }}"
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
