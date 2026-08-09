<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Ruangan
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan ruangan baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.rooms.partials.form', [
                'room' => new \App\Models\Room(),
                'action' => route('admin.rooms.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Ruangan',
            ])
        </div>
    </div>
</x-app-layout>