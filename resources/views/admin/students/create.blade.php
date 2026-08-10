<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Siswa
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan data master siswa baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.students.partials.form', [
                'student' => new \App\Models\Student(),
                'person' => new \App\Models\Person(),
                'action' => route('admin.students.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Siswa',
            ])
        </div>
    </div>
</x-app-layout>
