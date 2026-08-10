<form method="POST" action="{{ $action }}" class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="p-6 space-y-8">
        <div>
            <h3 class="font-semibold text-gray-900">Identitas Orang Tua/Wali</h3>

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
            <h3 class="font-semibold text-gray-900">Hubungan dengan Siswa</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="relationship" value="Hubungan" />

                    <select
                        id="relationship"
                        name="relationship"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        required
                    >
                        @foreach ([
                            'father' => 'Ayah',
                            'mother' => 'Ibu',
                            'guardian' => 'Wali',
                            'other' => 'Lainnya',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('relationship', $guardian->relationship ?: 'guardian') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('relationship')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="occupation" value="Pekerjaan" />

                    <x-text-input
                        id="occupation"
                        name="occupation"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('occupation', $guardian->occupation)"
                    />
                </div>

                <div>
                    <x-input-label for="education_level" value="Pendidikan Terakhir" />

                    <x-text-input
                        id="education_level"
                        name="education_level"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('education_level', $guardian->education_level)"
                    />
                </div>

                <div>
                    <x-input-label for="income_range" value="Rentang Penghasilan" />

                    <x-text-input
                        id="income_range"
                        name="income_range"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('income_range', $guardian->income_range)"
                        placeholder="Contoh: 1-3 juta"
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
                >{{ old('notes', $guardian->notes) }}</textarea>
            </div>

            <div class="mt-6 space-y-3">
                <label class="flex items-center gap-2">
                    <input
                        name="is_primary_contact"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_primary_contact', $guardian->is_primary_contact))
                    >

                    <span class="text-sm text-gray-700">
                        Kontak utama
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_emergency_contact"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_emergency_contact', $guardian->is_emergency_contact))
                    >

                    <span class="text-sm text-gray-700">
                        Kontak darurat
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_financial_responsible"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_financial_responsible', $guardian->is_financial_responsible))
                    >

                    <span class="text-sm text-gray-700">
                        Penanggung jawab keuangan
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_active', $guardian->is_active ?? true))
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
            href="{{ route('admin.students.guardians.index', $student) }}"
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
