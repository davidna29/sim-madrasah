<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Riwayat Kelas Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->person?->full_name }}
                </p>
            </div>

            @can('permission', 'student_class_histories.create')
                <a
                    href="{{ route('admin.students.class-histories.create', $student) }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Riwayat
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900">
                        Data Siswa
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                        <div>
                            <div class="text-gray-500">Nama</div>
                            <div class="font-medium text-gray-900">
                                {{ $student->person?->full_name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">NIS</div>
                            <div class="font-medium text-gray-900">
                                {{ $student->student_number ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">NISN</div>
                            <div class="font-medium text-gray-900">
                                {{ $student->nisn ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Tahun Ajaran
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Semester
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Rombel
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Ruangan
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Periode
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
                            @forelse ($histories as $history)
                                <tr>
                                    <td class="px-4 py-3">
                                        {{ $history->academicYear?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $history->semester?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $history->classGroup?->name ?? '-' }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ $history->classGroup?->gradeLevel?->name ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $history->classGroup?->room?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $history->start_date?->format('d M Y') ?? '-' }}
                                        sampai
                                        {{ $history->end_date?->format('d M Y') ?? 'sekarang' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ $history->status }}
                                            </span>

                                            @if ($history->is_current)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Saat Ini
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @can('permission', 'student_class_histories.update')
                                            <a
                                                href="{{ route('admin.students.class-histories.edit', [$student, $history]) }}"
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
                                        Belum ada riwayat kelas siswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $histories->links() }}
                    </div>
                </div>
            </div>

            <div>
                <a
                    href="{{ route('admin.students.index') }}"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    Kembali ke Data Siswa
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
