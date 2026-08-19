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
        <div class="rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-green-800">
            Jenis slot menentukan apakah slot dapat diisi mata pelajaran.
            Jika jenis slot bukan KBM, sistem otomatis menandainya sebagai non-KBM.
        </div>

        <div>
            <x-input-label for="day_of_week" value="Hari" />

            <select
                id="day_of_week"
                name="day_of_week"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ($days as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected((int) old('day_of_week', $slot->day_of_week) === (int) $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('day_of_week')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="sort_order" value="Urutan Slot" />

            <x-text-input
                id="sort_order"
                name="sort_order"
                type="number"
                min="1"
                max="{{ $scheduleTemplate->max_slots_per_day }}"
                class="mt-1 block w-full"
                :value="old('sort_order', $slot->sort_order)"
                required
            />

            <p class="mt-1 text-xs text-gray-500">
                Maksimal {{ $scheduleTemplate->max_slots_per_day }} slot per hari sesuai pengaturan template.
            </p>

            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-input-label for="starts_at" value="Jam Mulai" />

                <x-text-input
                    id="starts_at"
                    name="starts_at"
                    type="time"
                    class="mt-1 block w-full"
                    :value="old('starts_at', $slot->starts_at ? substr($slot->starts_at, 0, 5) : '')"
                    required
                />

                <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="ends_at" value="Jam Selesai" />

                <x-text-input
                    id="ends_at"
                    name="ends_at"
                    type="time"
                    class="mt-1 block w-full"
                    :value="old('ends_at', $slot->ends_at ? substr($slot->ends_at, 0, 5) : '')"
                    required
                />

                <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="slot_type" value="Jenis Slot" />

            <select
                id="slot_type"
                name="slot_type"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ($slotTypes as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('slot_type', $slot->slot_type ?: 'kbm') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('slot_type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="label" value="Label Slot" />

            <x-text-input
                id="label"
                name="label"
                type="text"
                class="mt-1 block w-full"
                :value="old('label', $slot->label)"
                placeholder="Contoh: Jam Pelajaran 1, Istirahat Pagi, Upacara"
            />

            <x-input-error :messages="$errors->get('label')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="notes" value="Catatan" />

            <textarea
                id="notes"
                name="notes"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                placeholder="Opsional. Contoh: khusus hari Jumat durasi lebih pendek."
            >{{ old('notes', $slot->notes) }}</textarea>

            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.schedule-templates.slots.index', $scheduleTemplate) }}"
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