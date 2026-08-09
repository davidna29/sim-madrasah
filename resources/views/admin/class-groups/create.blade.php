<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Rombongan Belajar
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat rombongan belajar baru untuk tahun ajaran tertentu.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.class-groups.partials.form', [
                'classGroup' => new \App\Models\ClassGroup(),
                'action' => route('admin.class-groups.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Rombel',
            ])
        </div>
    </div>
</x-app-layout>
