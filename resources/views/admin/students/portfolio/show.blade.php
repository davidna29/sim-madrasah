<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Portofolio Digital Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->person?->full_name }}
                </p>
            </div>

            <a
                href="{{ route('admin.students.index') }}"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Identitas Siswa
                        </h3>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                            <div>
                                <div class="text-gray-500">Nama Lengkap</div>
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

                            <div>
                                <div class="text-gray-500">Nomor Registrasi</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->registration_number ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Tahun Ajaran Masuk</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->admissionAcademicYear?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Tanggal Masuk</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->admission_date?->format('d M Y') ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Email</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->person?->email ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Telepon</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->person?->phone ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Status Ringkas
                        </h3>

                        <div class="mt-4 space-y-4 text-sm">
                            <div>
                                <div class="text-gray-500">Status Siswa</div>
                                <div class="mt-1">
                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        {{ $student->status }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Status Akun</div>

                                @if ($student->person?->user)
                                    <div class="mt-1 font-mono text-xs text-gray-900">
                                        {{ $student->person->user->username }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $student->person->user->status }}
                                    </div>
                                @else
                                    <div class="mt-1 text-gray-500">
                                        Belum memiliki akun.
                                    </div>
                                @endif
                            </div>

                            <div>
                                <div class="text-gray-500">Kelas Saat Ini</div>
                                @if ($student->currentClassHistory)
                                    <div class="mt-1 font-medium text-gray-900">
                                        {{ $student->currentClassHistory->classGroup?->name ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $student->currentClassHistory->semester?->name ?? '-' }}
                                    </div>
                                @else
                                    <div class="mt-1 text-gray-500">
                                        Belum ada kelas aktif.
                                    </div>
                                @endif
                            </div>

                            <!-- QR Code Portofolio -->
                            <div class="border-t border-gray-100 pt-4">
                                <div class="text-gray-500">
                                    QR Code Portofolio
                                </div>

                                <div class="mt-3 inline-block rounded-lg border border-gray-100 bg-white p-3">
                                    {!! $qrCodeSvg !!}
                                </div>

                                <div class="mt-3 break-all text-xs text-gray-500">
                                    {{ $portfolioUrl }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900">
                        Kelas Aktif
                    </h3>

                    @if ($student->currentClassHistory)
                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4 text-sm">
                            <div>
                                <div class="text-gray-500">Tahun Ajaran</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->academicYear?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Semester</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->semester?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Rombel</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->classGroup?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Ruangan</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->classGroup?->room?->name ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">
                            Siswa belum memiliki kelas aktif.
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Riwayat Kelas Terbaru
                        </h3>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Tahun
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Semester
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Rombel
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Status
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($classHistories as $history)
                                        <tr>
                                            <td class="px-4 py-3">
                                                {{ $history->academicYear?->name ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                {{ $history->semester?->name ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                {{ $history->classGroup?->name ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                @if ($history->is_current)
                                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                        Saat Ini
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                        {{ $history->status }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                                Belum ada riwayat kelas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Orang Tua/Wali
                        </h3>

                        <div class="mt-4 space-y-4">
                            @forelse ($guardians as $guardian)
                                <div class="rounded-lg border border-gray-100 p-4 text-sm">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ $guardian->person?->full_name }}
                                            </div>

                                            <div class="text-xs text-gray-500">
                                                {{ $guardian->relationship }}
                                            </div>
                                        </div>

                                        @if ($guardian->is_primary_contact)
                                            <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                Utama
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3 text-gray-600">
                                        {{ $guardian->person?->phone ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $guardian->person?->email ?? '-' }}
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">
                                    Belum ada data orang tua atau wali.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900">
                        Modul Portofolio Berikutnya
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach ([
                            'Kehadiran',
                            'Nilai',
                            'Prestasi',
                            'Pelanggaran',
                            'Konseling',
                            'Tahfidz',
                            'Pembiasaan',
                            'Pembayaran',
                            'Dokumen',
                        ] as $module)
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                                <div class="font-medium text-gray-900">
                                    {{ $module }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    Belum aktif. Akan dibuat pada tahap modul lanjutan.
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
