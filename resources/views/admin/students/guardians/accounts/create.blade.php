<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Buat Akun Orang Tua/Wali
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat akun login untuk orang tua atau wali siswa.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">
                    Data Siswa dan Wali
                </h3>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <div>
                        <div class="text-gray-500">Nama Siswa</div>
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
                        <div class="text-gray-500">Nama Wali</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->person?->full_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Hubungan</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->relationship }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Email Data Pribadi</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->person?->email ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Telepon</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->person?->phone ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.students.guardians.accounts.store', [$student, $guardian]) }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf

                <div class="p-6 space-y-6">
                    <div>
                        <x-input-label for="username" value="Username" />

                        <x-text-input
                            id="username"
                            name="username"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('username', $suggestedUsername)"
                            required
                        />

                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email Login" />

                        <x-text-input
                            id="email"
                            name="email"
                            type="email"
                            class="mt-1 block w-full"
                            :value="old('email', $guardian->person?->email)"
                            required
                        />

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password Awal" />

                        <x-text-input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Password" />

                        <x-text-input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <div>
                        <x-input-label for="role_id" value="Role Awal" />

                        <select
                            id="role_id"
                            name="role_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            <option value="">Pilih Role</option>

                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role->id }}"
                                    @selected((string) old('role_id') === (string) $role->id)
                                >
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status Akun" />

                        <select
                            id="status"
                            name="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            <option value="active" @selected(old('status', 'active') === 'active')>
                                Aktif
                            </option>

                            <option value="inactive" @selected(old('status') === 'inactive')>
                                Nonaktif
                            </option>
                        </select>

                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <a
                        href="{{ route('admin.students.guardians.index', $student) }}"
                        class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                    >
                        Buat Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
