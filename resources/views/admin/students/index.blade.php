<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Data Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data master siswa tanpa mengubah histori kelas.
                </p>
            </div>

            @can('permission', 'students.create')
                <a
                    href="{{ route('admin.students.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Siswa
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">NIS</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">NISN</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tahun Masuk</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $student->person?->full_name }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $student->registration_number ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $student->student_number ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $student->nisn ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $student->admissionAcademicYear?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>{{ $student->person?->email ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $student->person?->phone ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ $student->status }}
                                            </span>

                                            @if ($student->is_active)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @can('permission', 'students.update')
                                            <a
                                                href="{{ route('admin.students.edit', $student) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada data siswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
