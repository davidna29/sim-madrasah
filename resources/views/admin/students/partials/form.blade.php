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

                    <select
                        id="gender"
                        name="gender"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    >
                        <option value="">Pilih</option>
                        <option value="male" @selected(old('gender', $person->gender) === 'male')>
                            Laki-laki
                        </option>
                        <option value="female" @selected(old('gender', $person->gender) === 'female')>
                            Perempuan
                        </option>
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
            <h3 class="font-semibold text-gray-900">Data Kesiswaan</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="student_number" value="NIS" />

                    <x-text-input
                        id="student_number"
                        name="student_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('student_number', $student->student_number)"
                    />

                    <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nisn" value="NISN" />

                    <x-text-input
                        id="nisn"
                        name="nisn"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('nisn', $student->nisn)"
                    />

                    <x-input-error :messages="$errors->get('nisn')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="registration_number" value="Nomor Registrasi" />

                    <x-text-input
                        id="registration_number"
                        name="registration_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('registration_number', $student->registration_number)"
                    />

                    <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="admission_academic_year_id" value="Tahun Ajaran Masuk" />

                    <select
                        id="admission_academic_year_id"
                        name="admission_academic_year_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    >
                        <option value="">Pilih Tahun Ajaran</option>

                        @foreach ($academicYears as $academicYear)
                            <option
                                value="{{ $academicYear->id }}"
                                @selected((string) old('admission_academic_year_id', $student->admission_academic_year_id) === (string) $academicYear->id)
                            >
                                {{ $academicYear->name }}
                            </option>
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('admission_academic_year_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="admission_date" value="Tanggal Masuk" />

                    <x-text-input
                        id="admission_date"
                        name="admission_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('admission_date', $student->admission_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="graduation_date" value="Tanggal Lulus" />

                    <x-text-input
                        id="graduation_date"
                        name="graduation_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('graduation_date', $student->graduation_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="status" value="Status Siswa" />

                    <select
                        id="status"
                        name="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        required
                    >
                        @foreach ([
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'transferred' => 'Pindah',
                            'graduated' => 'Lulus',
                            'alumni' => 'Alumni',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('status', $student->status ?: 'active') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="previous_school" value="Sekolah Asal" />

                    <x-text-input
                        id="previous_school"
                        name="previous_school"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('previous_school', $student->previous_school)"
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
                >{{ old('notes', $student->notes) }}</textarea>
            </div>

            <div class="mt-6">
                <label class="flex items-center gap-2">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_active', $student->is_active ?? true))
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
            href="{{ route('admin.students.index') }}"
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
