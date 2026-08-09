<aside class="hidden lg:flex lg:w-72 lg:flex-col lg:border-r lg:border-gray-200 lg:bg-white">
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
            <div>
                <div class="text-base font-bold text-green-700">
                    SIM Madrasah
                </div>

                <div class="text-xs text-gray-500">
                    Sistem Informasi Manajemen
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <x-ui.sidebar-section title="Utama">
                <x-ui.sidebar-link
                    :href="route('dashboard')"
                    :active="request()->routeIs('dashboard')"
                >
                    Dashboard
                </x-ui.sidebar-link>
            </x-ui.sidebar-section>

            @if (
                    auth()->user()?->can('permission', 'madrasah.view') ||
                    auth()->user()?->can('permission', 'roles.view') ||
                    auth()->user()?->can('permission', 'permissions.view')
                )
                <x-ui.sidebar-section title="Administrasi Sistem">
                   <!-- Identitas Madrasah -->
                    @can('permission', 'madrasah.view')
    <x-ui.sidebar-link
        :href="route('admin.madrasah.edit')"
        :active="request()->routeIs('admin.madrasah.*')"
    >
        Identitas Madrasah
    </x-ui.sidebar-link>
@endcan
                
                @can('permission', 'roles.view')
                        <x-ui.sidebar-link
                            :href="route('admin.roles.index')"
                            :active="request()->routeIs('admin.roles.*')"
                        >
                            Role
                        </x-ui.sidebar-link>
                    @endcan

                    @can('permission', 'permissions.view')
                        <x-ui.sidebar-link
                            :href="route('admin.permissions.index')"
                            :active="request()->routeIs('admin.permissions.*')"
                        >
                            Permission
                        </x-ui.sidebar-link>
                    @endcan                    
                </x-ui.sidebar-section>
            @endif

            <x-ui.sidebar-section title="Akun">
                <x-ui.sidebar-link
                    :href="route('profile.edit')"
                    :active="request()->routeIs('profile.edit')"
                >
                    Profil Saya
                </x-ui.sidebar-link>
            </x-ui.sidebar-section>

            <x-ui.sidebar-section title="Modul Berikutnya">
                <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-500">
                    Modul siswa, guru, akademik, keuangan, PPDB, PKKM, dan akreditasi akan aktif setelah route dan database modul dibuat.
                </div>
            </x-ui.sidebar-section>
        </nav>
    </div>
</aside>
