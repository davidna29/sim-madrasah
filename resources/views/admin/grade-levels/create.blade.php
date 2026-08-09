<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Tingkat Kelas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan tingkat kelas baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.grade-levels.partials.form', [
                'gradeLevel' => new \App\Models\GradeLevel(),
                'action' => route('admin.grade-levels.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Tingkat Kelas',
            ])
        </div>
    </div>
</x-app-layout>