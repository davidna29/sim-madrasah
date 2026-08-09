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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Status Akun
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ ucfirst($user->status) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Jenis Akun
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ ucfirst($user->account_type) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Role Aktif
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ $roles->count() }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Permission Aktif
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ $permissionCount }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Role Pengguna
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Role menentukan kelompok hak akses pengguna di dalam SIM Madrasah.
                    </p>

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
                <h3 class="text-lg font-semibold text-gray-900">
                    Menu Kerja Utama
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Widget berikut disesuaikan dengan role pengguna. Data asli akan ditampilkan setelah modul terkait tersedia.
                </p>

                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach ($widgets as $widget)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="font-semibold text-gray-900">
                                    {{ $widget['title'] }}
                                </h4>

                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $widget['description'] }}
                                </p>

                                <div class="mt-4 text-xs font-medium text-gray-400">
                                    Menunggu modul terkait
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Informasi Keamanan Akun
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                        <div>
                            <div class="text-gray-500">
                                Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">
                                IP Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_ip ?? '-' }}
                            </div>
                        </div>

                        <div>
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

        </div>
    </div>
</x-app-layout>