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
            <x-input-label for="code" value="Kode Ruangan" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $room->code)"
                placeholder="LAB-KOM"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Ruangan" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $room->name)"
                placeholder="Laboratorium Komputer"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="room_type" value="Jenis Ruangan" />

            <select
                id="room_type"
                name="room_type"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ([
                    'classroom' => 'Ruang Kelas',
                    'laboratory' => 'Laboratorium',
                    'library' => 'Perpustakaan',
                    'office' => 'Kantor',
                    'hall' => 'Aula',
                    'other' => 'Lainnya',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('room_type', $room->room_type ?: 'classroom') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('room_type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="capacity" value="Kapasitas" />

            <x-text-input
                id="capacity"
                name="capacity"
                type="number"
                min="1"
                class="mt-1 block w-full"
                :value="old('capacity', $room->capacity)"
            />

            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="location" value="Lokasi" />

            <x-text-input
                id="location"
                name="location"
                type="text"
                class="mt-1 block w-full"
                :value="old('location', $room->location)"
                placeholder="Gedung Utama Lantai 1"
            />

            <x-input-error :messages="$errors->get('location')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />

            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >{{ old('description', $room->description) }}</textarea>

            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                @checked(old('is_active', $room->is_active ?? true))
            >

            <label for="is_active" class="text-sm text-gray-700">
                Aktif
            </label>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.rooms.index') }}"
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
