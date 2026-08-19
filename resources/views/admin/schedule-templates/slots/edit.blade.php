<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Slot Template Jadwal
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $scheduleTemplate->name }} · {{ $scheduleTemplate->code }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.schedule-templates.slots.partials.form', [
                'scheduleTemplate' => $scheduleTemplate,
                'slot' => $slot,
                'days' => $days,
                'slotTypes' => $slotTypes,
                'action' => route('admin.schedule-template-slots.update', $slot),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Slot',
            ])
        </div>
    </div>
</x-app-layout>