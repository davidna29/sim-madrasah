<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Orang Tua/Wali Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->person?->full_name }}
                </p>
            </div>

            @can('permission', 'student_guardians.create')
                <a
                    href="{{ route('admin.students.guardians.create', $student) }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Wali
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Hubungan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Akun</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Pekerjaan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Peran</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($guardians as $guardian)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $guardian->person?->full_name }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ $guardian->person?->national_id_number ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $guardian->relationship }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>{{ $guardian->person?->email ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $guardian->person?->phone ?? '-' }}
                                        </div>
                                    </td>
                                    <!-- Update View Orang Tua/Wali -->
                                    <td class="px-4 py-3">
                                        @if ($guardian->person?->user)
                                            <div class="font-mono text-xs text-gray-900">
                                                {{ $guardian->person->user->username }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $guardian->person->user->status }}
                                            </div>
                                        @else
                                            @can('permission', 'student_guardians.account.create')
                                                <a
                                                    href="{{ route('admin.students.guardians.accounts.create', [$student, $guardian]) }}"
                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                >
                                                    Buat Akun
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-500">
                                                    Belum ada akun
                                                </span>
                                            @endcan
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $guardian->occupation ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($guardian->is_primary_contact)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Kontak Utama
                                                </span>
                                            @endif

                                            @if ($guardian->is_emergency_contact)
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                    Darurat
                                                </span>
                                            @endif

                                            @if ($guardian->is_financial_responsible)
                                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                                    Keuangan
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($guardian->is_active)
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
                                        @can('permission', 'student_guardians.update')
                                            <a
                                                href="{{ route('admin.students.guardians.edit', [$student, $guardian]) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada data orang tua atau wali.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $guardians->links() }}
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
