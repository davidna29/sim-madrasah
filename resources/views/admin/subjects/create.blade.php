<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Mata Pelajaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan mata pelajaran baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.subjects.partials.form', [
                'subject' => new \App\Models\Subject(),
                'action' => route('admin.subjects.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Mata Pelajaran',
            ])
        </div>
    </div>
</x-app-layout>
