<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $dashboardTitle }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Selamat datang, {{ $user->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                @foreach ($summaryCards as $card)
                    <x-ui.stat-card
                        :label="$card['label']"
                        :value="$card['value']"
                        :description="$card['description']"
                    />
                @endforeach
            </div>
@if (! empty($systemCards))
    <div>
        <div class="flex items-end justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Ringkasan Sistem
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Data umum sistem yang hanya ditampilkan untuk Super Admin.
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-4">
            @foreach ($systemCards as $card)
                <x-ui.stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :description="$card['description']"
                />
            @endforeach
        </div>
    </div>
@endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Role Pengguna
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Role menentukan kelompok hak akses pengguna di dalam SIM Madrasah.
                            </p>
                        </div>

                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                            {{ $roles->count() }} role aktif
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($roles as $role)
                            <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">
                                {{ $role->display_name }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">
                                Belum memiliki role aktif.
                            </span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Navigasi Modul
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Menu kerja utama disesuaikan dengan role pengguna.
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ($moduleCards as $module)
                        <x-ui.module-card
                            :title="$module['title']"
                            :description="$module['description']"
                            :href="$module['href']"
                        />
                    @endforeach
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Keamanan Akun
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Informasi ini membantu memantau keamanan akun pengguna.
                    </p>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <div class="text-gray-500">
                                Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <div class="text-gray-500">
                                IP Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_ip ?? '-' }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <div class="text-gray-500">
                                Password Diubah
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->password_changed_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-dashed border-green-200 bg-green-50 p-5">
                <h3 class="font-semibold text-green-900">
                    Catatan Pengembangan
                </h3>

                <p class="mt-1 text-sm text-green-800">
                    Dashboard ini masih menggunakan data fondasi. Widget akademik, keuangan, kesiswaan, PPDB, PKKM, dan portofolio akan dihubungkan setelah modul terkait selesai dibuat.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>