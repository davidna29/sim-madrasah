<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Template Jadwal
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola model jadwal sebelum digunakan oleh rombongan belajar.
                </p>
            </div>

            @can('permission', 'schedule_templates.create')
                <a
                    href="{{ route('admin.schedule-templates.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Template
                </a>
            @endcan
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
                Template jadwal hanya mengatur pola hari dan batas umum jadwal.
                Slot jam, assignment rombel, jadwal manual, dan auto-generate dibuat pada tahap berikutnya.
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kode
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Template
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Hari Aktif
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Batas Jam
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Relasi
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($scheduleTemplates as $template)
                                @php
                                    $activeDayLabels = collect($template->active_days ?? [])
                                        ->map(fn ($day) => $days[(int) $day] ?? $day)
                                        ->join(', ');

                                    $holidayDayLabels = collect($template->holiday_days ?? [])
                                        ->map(fn ($day) => $days[(int) $day] ?? $day)
                                        ->join(', ');
                                @endphp

                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $template->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $template->name }}
                                        </div>

                                        @if ($template->description)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $template->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="text-gray-900">
                                            {{ $activeDayLabels ?: '-' }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            Libur: {{ $holidayDayLabels ?: '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>
                                            Maks. {{ $template->max_slots_per_day }} slot/hari
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            Standar {{ $template->standard_slot_duration_minutes }} menit/slot
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-2">
                                            <span class="w-fit rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ ucfirst($template->status) }}
                                            </span>

                                            @if ($template->is_active)
                                                <span class="w-fit rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="w-fit rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>
                                            {{ $template->slots_count }} slot
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $template->class_group_assignments_count }} rombel
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                             @can('permission', 'schedule_templates.view')
                                                <a
                                                    href="{{ route('admin.schedule-templates.slots.index', $template) }}"
                                                    class="text-sm font-medium text-purple-700 hover:text-purple-900"
                                                >
                                                    Slot
                                                </a>
                                            @endcan

                                            @can('permission', 'schedule_templates.update')
                                                <a
                                                    href="{{ route('admin.schedule-templates.edit', $template) }}"
                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                >
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('permission', 'schedule_templates.create')
                                                <form method="POST" action="{{ route('admin.schedule-templates.clone', $template) }}">
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="text-sm font-medium text-blue-700 hover:text-blue-900"
                                                    >
                                                        Duplikat
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('permission', 'schedule_templates.delete')
                                                @if (! $template->is_active && $template->class_group_assignments_count === 0)
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin.schedule-templates.destroy', $template) }}"
                                                        onsubmit="return confirm('Hapus template jadwal ini?')"
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
                                                @else
                                                    <span class="text-xs text-gray-400">
                                                        Hapus terkunci
                                                    </span>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada template jadwal.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $scheduleTemplates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>