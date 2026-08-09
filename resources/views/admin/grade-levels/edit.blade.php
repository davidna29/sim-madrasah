<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Tingkat Kelas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data tingkat kelas.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.grade-levels.partials.form', [
                'gradeLevel' => $gradeLevel,
                'action' => route('admin.grade-levels.update', $gradeLevel),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Tingkat Kelas',
            ])
        </div>
    </div>
</x-app-layout>