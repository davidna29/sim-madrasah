<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Tahun Ajaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat tahun ajaran beserta semester ganjil dan genap.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('admin.academic-years.store') }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="code" value="Kode Tahun Ajaran" />

                            <x-text-input
                                id="code"
                                name="code"
                                type="text"
                                class="mt-1 block w-full"
                                value="{{ old('code') }}"
                                placeholder="2026-2027"
                                required
                            />

                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="name" value="Nama Tahun Ajaran" />

                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                value="{{ old('name') }}"
                                placeholder="2026/2027"
                                required
                            />

                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai Tahun Ajaran" />

                            <x-text-input
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="mt-1 block w-full"
                                value="{{ old('start_date') }}"
                                required
                            />

                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="end_date" value="Tanggal Selesai Tahun Ajaran" />

                            <x-text-input
                                id="end_date"
                                name="end_date"
                                type="date"
                                class="mt-1 block w-full"
                                value="{{ old('end_date') }}"
                                required
                            />

                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <h3 class="font-semibold text-gray-900">
                            Semester Ganjil
                        </h3>

                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="ganjil_start_date" value="Mulai" />

                                <x-text-input
                                    id="ganjil_start_date"
                                    name="ganjil_start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('ganjil_start_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('ganjil_start_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="ganjil_end_date" value="Selesai" />

                                <x-text-input
                                    id="ganjil_end_date"
                                    name="ganjil_end_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('ganjil_end_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('ganjil_end_date')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <h3 class="font-semibold text-gray-900">
                            Semester Genap
                        </h3>

                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="genap_start_date" value="Mulai" />

                                <x-text-input
                                    id="genap_start_date"
                                    name="genap_start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('genap_start_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('genap_start_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="genap_end_date" value="Selesai" />

                                <x-text-input
                                    id="genap_end_date"
                                    name="genap_end_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('genap_end_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('genap_end_date')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <a
                        href="{{ route('admin.academic-years.index') }}"
                        class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                    >
                        Simpan Tahun Ajaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>