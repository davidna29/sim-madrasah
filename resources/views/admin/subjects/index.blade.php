<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mata Pelajaran
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data master mata pelajaran.
                </p>
            </div>

            @can('permission', 'subjects.create')
                <a
                    href="{{ route('admin.subjects.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Mata Pelajaran
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kode
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Mata Pelajaran
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kelompok
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Jenis
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($subjects as $subject)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $subject->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $subject->name }}
                                        </div>

                                        @if ($subject->description)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $subject->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $subject->subject_group }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($subject->is_religious)
                                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                                    Keagamaan
                                                </span>
                                            @endif

                                            @if ($subject->is_local_content)
                                                <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700">
                                                    Muatan Lokal
                                                </span>
                                            @endif

                                            @unless ($subject->is_religious || $subject->is_local_content)
                                                <span class="text-gray-500">
                                                    Umum
                                                </span>
                                            @endunless
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($subject->is_active)
                                            <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            @can('permission', 'subjects.update')
                                                <a
                                                    href="{{ route('admin.subjects.edit', $subject) }}"
                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                >
                                                    Edit
                                                </a>

                                                <form method="POST" action="{{ route('admin.subjects.toggle-active', $subject) }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <button
                                                        type="submit"
                                                        class="text-sm font-medium {{ $subject->is_active ? 'text-red-700 hover:text-red-900' : 'text-green-700 hover:text-green-900' }}"
                                                    >
                                                        {{ $subject->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada mata pelajaran.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $subjects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
