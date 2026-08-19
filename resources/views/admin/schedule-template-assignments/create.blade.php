<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Assignment Template Jadwal
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Pilih rombel dan template jadwal yang akan dipakai pada semester tertentu.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.schedule-template-assignments.partials.form', [
                'assignment' => $assignment,
                'academicYears' => $academicYears,
                'semesters' => $semesters,
                'classGroups' => $classGroups,
                'scheduleTemplates' => $scheduleTemplates,
                'action' => route('admin.schedule-template-assignments.store'),
                'buttonLabel' => 'Simpan Assignment',
            ])
        </div>
    </div>
</x-app-layout>