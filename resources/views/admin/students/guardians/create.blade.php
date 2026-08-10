<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Orang Tua/Wali
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $student->person?->full_name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.students.guardians.partials.form', [
                'student' => $student,
                'guardian' => $guardian,
                'person' => $person,
                'action' => route('admin.students.guardians.store', $student),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Wali',
            ])
        </div>
    </div>
</x-app-layout>
