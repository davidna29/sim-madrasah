@php
    $selectedActiveDays = collect(old('active_days', $scheduleTemplate->active_days ?? []))
        ->map(fn ($day) => (int) $day)
        ->all();

    $selectedHolidayDays = collect(old('holiday_days', $scheduleTemplate->holiday_days ?? []))
        ->map(fn ($day) => (int) $day)
        ->all();
@endphp

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
            <x-input-label for="code" value="Kode Template" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $scheduleTemplate->code)"
                placeholder="REGULER-5-HARI"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Template" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $scheduleTemplate->name)"
                placeholder="Template Reguler 5 Hari"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />

            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                placeholder="Contoh: Dipakai untuk jadwal reguler Senin sampai Jumat."
            >{{ old('description', $scheduleTemplate->description) }}</textarea>

            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-input-label value="Hari Aktif" />

                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($days as $value => $label)
                        <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm">
                            <input
                                name="active_days[]"
                                type="checkbox"
                                value="{{ $value }}"
                                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                                @checked(in_array((int) $value, $selectedActiveDays, true))
                            >

                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <x-input-error :messages="$errors->get('active_days')" class="mt-2" />
                <x-input-error :messages="$errors->get('active_days.*')" class="mt-2" />
            </div>

            <div>
                <x-input-label value="Hari Libur" />

                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($days as $value => $label)
                        <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm">
                            <input
                                name="holiday_days[]"
                                type="checkbox"
                                value="{{ $value }}"
                                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                                @checked(in_array((int) $value, $selectedHolidayDays, true))
                            >

                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <x-input-error :messages="$errors->get('holiday_days')" class="mt-2" />
                <x-input-error :messages="$errors->get('holiday_days.*')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <x-input-label for="max_slots_per_day" value="Maksimal Slot per Hari" />

                <x-text-input
                    id="max_slots_per_day"
                    name="max_slots_per_day"
                    type="number"
                    min="1"
                    max="20"
                    class="mt-1 block w-full"
                    :value="old('max_slots_per_day', $scheduleTemplate->max_slots_per_day ?? 10)"
                    required
                />

                <x-input-error :messages="$errors->get('max_slots_per_day')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="standard_slot_duration_minutes" value="Durasi Standar per Slot" />

                <x-text-input
                    id="standard_slot_duration_minutes"
                    name="standard_slot_duration_minutes"
                    type="number"
                    min="10"
                    max="120"
                    class="mt-1 block w-full"
                    :value="old('standard_slot_duration_minutes', $scheduleTemplate->standard_slot_duration_minutes ?? 35)"
                    required
                />

                <p class="mt-1 text-xs text-gray-500">
                    Satuan menit. Nanti slot tetap bisa dibuat berbeda untuk kasus khusus.
                </p>

                <x-input-error :messages="$errors->get('standard_slot_duration_minutes')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="status" value="Status Penyusunan" />

            <select
                id="status"
                name="status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ([
                    'draft' => 'Draft',
                    'ready' => 'Siap Dipakai',
                    'archived' => 'Diarsipkan',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('status', $scheduleTemplate->status ?: 'draft') === $value)
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
                @checked(old('is_active', $scheduleTemplate->is_active ?? true))
            >

            <label for="is_active" class="text-sm text-gray-700">
                Template aktif dan boleh dipakai
            </label>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.schedule-templates.index') }}"
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