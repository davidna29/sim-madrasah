<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Slot Template Jadwal
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $scheduleTemplate->name }} · {{ $scheduleTemplate->code }}
                </p>
            </div>

            <div class="flex gap-3">
                <a
                    href="{{ route('admin.schedule-templates.index') }}"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Kembali
                </a>

                @can('permission', 'schedule_templates.update')
                    @if ($canManageSlots)
                        <a
                            href="{{ route('admin.schedule-templates.slots.create', $scheduleTemplate) }}"
                            class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                        >
                            Tambah Slot
                        </a>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-green-800">
                Slot KBM akan menjadi tempat mata pelajaran pada tahap jadwal manual dan auto-generate.
                Slot non-KBM seperti istirahat, upacara, dan kegiatan rutin tidak boleh diisi mata pelajaran.
            </div>

            @unless ($canManageSlots)
                <div class="mb-6 rounded-lg border border-yellow-100 bg-yellow-50 p-4 text-sm text-yellow-800">
                    Template ini sudah dipakai oleh rombel. Slot dikunci agar perubahan tidak mengganggu jadwal rombel.
                </div>
            @endunless

            <div class="space-y-6">
                @foreach ($days as $dayValue => $dayLabel)
                    @php
                        $daySlots = $slots->get($dayValue, collect());
                    @endphp

                    <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h3 class="font-semibold text-gray-900">
                                {{ $dayLabel }}
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Urutan
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Waktu
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Jenis
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Label
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Status Slot
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($daySlots as $slot)
                                        <tr>
                                            <td class="px-4 py-3">
                                                {{ $slot->sort_order }}
                                            </td>

                                            <td class="px-4 py-3 font-mono">
                                                {{ substr($slot->starts_at, 0, 5) }} - {{ substr($slot->ends_at, 0, 5) }}
                                            </td>

                                            <td class="px-4 py-3">
                                                {{ $slotTypes[$slot->slot_type] ?? $slot->slot_type }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900">
                                                    {{ $slot->label ?: '-' }}
                                                </div>

                                                @if ($slot->notes)
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        {{ $slot->notes }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                @if ($slot->is_teaching_slot)
                                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                        Bisa diisi mapel
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                        Non-KBM
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                @can('permission', 'schedule_templates.update')
                                                    @if ($canManageSlots)
                                                        <div class="flex flex-wrap gap-3">
                                                            <a
                                                                href="{{ route('admin.schedule-template-slots.edit', $slot) }}"
                                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                                            >
                                                                Edit
                                                            </a>

                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.schedule-template-slots.destroy', $slot) }}"
                                                                onsubmit="return confirm('Hapus slot template ini?')"
                                                            >
                                                                @csrf
                                                                @method('DELETE')

                                                                <button
                                                                    type="submit"
                                                                    class="text-sm font-medium text-red-700 hover:text-red-900"
                                                                >
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="text-xs text-gray-400">
                                                            Terkunci
                                                        </span>
                                                    @endif
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                                Belum ada slot pada hari ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>