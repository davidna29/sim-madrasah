<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Template Jadwal
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui pola dasar template jadwal.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.schedule-templates.partials.form', [
                'scheduleTemplate' => $scheduleTemplate,
                'days' => $days,
                'action' => route('admin.schedule-templates.update', $scheduleTemplate),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Template',
            ])
        </div>
    </div>
</x-app-layout>