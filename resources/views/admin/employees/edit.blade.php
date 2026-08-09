<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Guru/Pegawai
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data guru atau tenaga kependidikan.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.employees.partials.form', [
                'employee' => $employee,
                'person' => $employee->person,
                'action' => route('admin.employees.update', $employee),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Data',
            ])
        </div>
    </div>
</x-app-layout>
