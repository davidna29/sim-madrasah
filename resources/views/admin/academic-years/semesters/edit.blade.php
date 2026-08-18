<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Semester
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $semester->academicYear?->name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('admin.semesters.update', $semester) }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="code" value="Kode Semester" />

                            <x-text-input
                                id="code"
                                name="code"
                                type="text"
                                class="mt-1 block w-full"
                                value="{{ old('code', $semester->code) }}"
                                required
                            />

                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="name" value="Nama Semester" />

                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                value="{{ old('name', $semester->name) }}"
                                required
                            />

                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="semester_type" value="Jenis Semester" />

                            <select
                                id="semester_type"
                                name="semester_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                required
                            >
                                @foreach (['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('semester_type', $semester->semester_type) === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <x-input-error :messages="$errors->get('semester_type')" class="mt-2" />
                        </div>

                        <div></div>

                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai" />

                            <x-text-input
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="mt-1 block w-full"
                                value="{{ old('start_date', optional($semester->start_date)->format('Y-m-d')) }}"
                                required
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
                                value="{{ old('end_date', optional($semester->end_date)->format('Y-m-d')) }}"
                                required
                            />

                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
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
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>