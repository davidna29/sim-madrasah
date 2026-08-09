<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Identitas Madrasah
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data dasar madrasah yang digunakan di seluruh sistem.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('admin.madrasah.update') }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label
                                for="name"
                                value="Nama Madrasah"
                            />

                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name', $madrasah->name)"
                                required
                            />

                            <x-input-error
                                :messages="$errors->get('name')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="timezone"
                                value="Zona Waktu"
                            />

                            <x-text-input
                                id="timezone"
                                name="timezone"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('timezone', $madrasah->timezone)"
                                required
                            />

                            <x-input-error
                                :messages="$errors->get('timezone')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="nsm"
                                value="NSM"
                            />

                            <x-text-input
                                id="nsm"
                                name="nsm"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('nsm', $madrasah->nsm)"
                            />

                            <x-input-error
                                :messages="$errors->get('nsm')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="npsn"
                                value="NPSN"
                            />

                            <x-text-input
                                id="npsn"
                                name="npsn"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('npsn', $madrasah->npsn)"
                            />

                            <x-input-error
                                :messages="$errors->get('npsn')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="email"
                                value="Email"
                            />

                            <x-text-input
                                id="email"
                                name="email"
                                type="email"
                                class="mt-1 block w-full"
                                :value="old('email', $madrasah->email)"
                            />

                            <x-input-error
                                :messages="$errors->get('email')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="phone"
                                value="Telepon"
                            />

                            <x-text-input
                                id="phone"
                                name="phone"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('phone', $madrasah->phone)"
                            />

                            <x-input-error
                                :messages="$errors->get('phone')"
                                class="mt-2"
                            />
                        </div>
                    </div>

                    <div>
                        <x-input-label
                            for="address"
                            value="Alamat"
                        />

                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        >{{ old('address', $madrasah->address) }}</textarea>

                        <x-input-error
                            :messages="$errors->get('address')"
                            class="mt-2"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label
                                for="village"
                                value="Desa/Kelurahan"
                            />

                            <x-text-input
                                id="village"
                                name="village"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('village', $madrasah->village)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="district"
                                value="Kecamatan"
                            />

                            <x-text-input
                                id="district"
                                name="district"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('district', $madrasah->district)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="city"
                                value="Kabupaten/Kota"
                            />

                            <x-text-input
                                id="city"
                                name="city"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('city', $madrasah->city)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="province"
                                value="Provinsi"
                            />

                            <x-text-input
                                id="province"
                                name="province"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('province', $madrasah->province)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="postal_code"
                                value="Kode Pos"
                            />

                            <x-text-input
                                id="postal_code"
                                name="postal_code"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('postal_code', $madrasah->postal_code)"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            name="is_active"
                            type="checkbox"
                            value="1"
                            class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                            @checked(old('is_active', $madrasah->is_active))
                        >

                        <label
                            for="is_active"
                            class="text-sm text-gray-700"
                        >
                            Madrasah aktif
                        </label>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-100 bg-gray-50 px-6 py-4">
                    @can('permission', 'madrasah.update')
                        <button
                            type="submit"
                            class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                        >
                            Simpan Identitas
                        </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
