<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Siswa
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data master siswa.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.students.partials.form', [
                'student' => $student,
                'person' => $student->person,
                'action' => route('admin.students.update', $student),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Siswa',
            ])
        </div>
    </div>
</x-app-layout>
