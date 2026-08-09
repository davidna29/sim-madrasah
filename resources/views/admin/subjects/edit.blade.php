<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Mata Pelajaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data mata pelajaran.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.subjects.partials.form', [
                'subject' => $subject,
                'action' => route('admin.subjects.update', $subject),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Mata Pelajaran',
            ])
        </div>
    </div>
</x-app-layout>
