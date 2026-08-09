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
            <x-input-label for="code" value="Kode" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $gradeLevel->code)"
                placeholder="VII"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Tingkat Kelas" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $gradeLevel->name)"
                placeholder="Kelas VII"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="level_number" value="Nomor Tingkat" />

            <x-text-input
                id="level_number"
                name="level_number"
                type="number"
                min="1"
                max="12"
                class="mt-1 block w-full"
                :value="old('level_number', $gradeLevel->level_number)"
                required
            />

            <x-input-error :messages="$errors->get('level_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />

            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >{{ old('description', $gradeLevel->description) }}</textarea>

            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                @checked(old('is_active', $gradeLevel->is_active ?? true))
            >

            <label for="is_active" class="text-sm text-gray-700">
                Aktif
            </label>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.grade-levels.index') }}"
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