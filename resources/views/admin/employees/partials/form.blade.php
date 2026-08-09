<form method="POST" action="{{ $action }}" class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="p-6 space-y-8">
        <div>
            <h3 class="font-semibold text-gray-900">Identitas Pribadi</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="full_name" value="Nama Lengkap" />

                    <x-text-input
                        id="full_name"
                        name="full_name"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('full_name', $person->full_name)"
                        required
                    />

                    <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="national_id_number" value="NIK" />

                    <x-text-input
                        id="national_id_number"
                        name="national_id_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('national_id_number', $person->national_id_number)"
                    />

                    <x-input-error :messages="$errors->get('national_id_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="birth_place" value="Tempat Lahir" />

                    <x-text-input
                        id="birth_place"
                        name="birth_place"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('birth_place', $person->birth_place)"
                    />
                </div>

                <div>
                    <x-input-label for="birth_date" value="Tanggal Lahir" />

                    <x-text-input
                        id="birth_date"
                        name="birth_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('birth_date', $person->birth_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="gender" value="Jenis Kelamin" />

                    <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <option value="">Pilih</option>
                        <option value="male" @selected(old('gender', $person->gender) === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender', $person->gender) === 'female')>Perempuan</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="religion" value="Agama" />

                    <x-text-input
                        id="religion"
                        name="religion"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('religion', $person->religion)"
                    />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />

                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        class="mt-1 block w-full"
                        :value="old('email', $person->email)"
                    />

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="phone" value="Telepon" />

                    <x-text-input
                        id="phone"
                        name="phone"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('phone', $person->phone)"
                    />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="address" value="Alamat" />

                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >{{ old('address', $person->address) }}</textarea>
            </div>
        </div>

        <div>
            <h3 class="font-semibold text-gray-900">Data Kepegawaian</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="employee_number" value="Nomor Pegawai" />

                    <x-text-input
                        id="employee_number"
                        name="employee_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('employee_number', $employee->employee_number)"
                    />

                    <x-input-error :messages="$errors->get('employee_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nip" value="NIP" />

                    <x-text-input
                        id="nip"
                        name="nip"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('nip', $employee->nip)"
                    />
                </div>

                <div>
                    <x-input-label for="nuptk" value="NUPTK" />

                    <x-text-input
                        id="nuptk"
                        name="nuptk"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('nuptk', $employee->nuptk)"
                    />
                </div>

                <div>
                    <x-input-label for="employee_type" value="Jenis Pegawai" />

                    <select
                        id="employee_type"
                        name="employee_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        required
                    >
                        <option value="teacher" @selected(old('employee_type', $employee->employee_type ?: 'teacher') === 'teacher')>
                            Guru
                        </option>
                        <option value="staff" @selected(old('employee_type', $employee->employee_type) === 'staff')>
                            Tenaga Kependidikan
                        </option>
                    </select>
                </div>

                <div>
                    <x-input-label for="employment_status" value="Status Kepegawaian" />

                    <x-text-input
                        id="employment_status"
                        name="employment_status"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('employment_status', $employee->employment_status)"
                        placeholder="permanent / contract / honorary"
                    />
                </div>

                <div>
                    <x-input-label for="position" value="Jabatan" />

                    <x-text-input
                        id="position"
                        name="position"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('position', $employee->position)"
                        placeholder="Guru Mata Pelajaran"
                    />
                </div>

                <div>
                    <x-input-label for="join_date" value="Tanggal Mulai" />

                    <x-text-input
                        id="join_date"
                        name="join_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('join_date', $employee->join_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="end_date" value="Tanggal Selesai" />

                    <x-text-input
                        id="end_date"
                        name="end_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('end_date', $employee->end_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="education_level" value="Pendidikan Terakhir" />

                    <x-text-input
                        id="education_level"
                        name="education_level"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('education_level', $employee->education_level)"
                        placeholder="S1 / S2"
                    />
                </div>

                <div>
                    <x-input-label for="major" value="Jurusan" />

                    <x-text-input
                        id="major"
                        name="major"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('major', $employee->major)"
                    />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="notes" value="Catatan" />

                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >{{ old('notes', $employee->notes) }}</textarea>
            </div>

            <div class="mt-6 space-y-3">
                <label class="flex items-center gap-2">
                    <input
                        name="is_teacher"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_teacher', $employee->is_teacher))
                    >

                    <span class="text-sm text-gray-700">
                        Termasuk guru
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_active', $employee->is_active ?? true))
                    >

                    <span class="text-sm text-gray-700">
                        Aktif
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.employees.index') }}"
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
