<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Rombongan Belajar
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data rombongan belajar.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.class-groups.partials.form', [
                'classGroup' => $classGroup,
                'action' => route('admin.class-groups.update', $classGroup),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Rombel',
            ])
        </div>
    </div>
</x-app-layout>
