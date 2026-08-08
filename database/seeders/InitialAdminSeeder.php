<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class InitialAdminSeeder extends Seeder
{
    /**
     * Membuat administrator awal SIM Madrasah.
     */
    public function run(): void
    {
        /*
         * Seeder ini hanya digunakan pada tahap instalasi lokal.
         *
         * Pembuatan administrator production akan kita atur
         * secara khusus ketika masuk tahap deployment.
         */
        if (! app()->environment('local')) {
            throw new RuntimeException(
                'InitialAdminSeeder hanya boleh dijalankan pada environment local.'
            );
        }

        $name = trim(
            (string) config('sim.initial_admin.name')
        );

        $username = trim(
            (string) config('sim.initial_admin.username')
        );

        $email = trim(
            (string) config('sim.initial_admin.email')
        );

        $password = (string) config(
            'sim.initial_admin.password'
        );

        /*
         * Tidak menyediakan password bawaan.
         */
        if (
            $name === '' ||
            $username === '' ||
            $email === '' ||
            $password === ''
        ) {
            throw new RuntimeException(
                'Konfigurasi Initial Administrator belum lengkap.'
            );
        }

        DB::transaction(function () use (
            $name,
            $username,
            $email,
            $password
        ): void {

            /*
             * Cari akun lama jika sebelumnya administrator
             * pernah dibuat melalui Tinker atau seeder.
             */
            $user = User::withTrashed()
                ->where('username', $username)
                ->orWhere('email', $email)
                ->first();

            /*
             * Jika user sudah memiliki person,
             * gunakan person tersebut.
             */
            $person = $user?->person;

            /*
             * Jika belum memiliki person, cari berdasarkan email.
             */
            if ($person === null) {
                $person = Person::query()
                    ->where('email', $email)
                    ->first();
            }

            /*
             * Jika identitas sama sekali belum tersedia,
             * buat identitas baru.
             */
            if ($person === null) {
                $person = Person::create([
                    'full_name' => $name,
                    'email' => $email,
                ]);
            } else {
                $person->update([
                    'full_name' => $name,
                    'email' => $email,
                ]);
            }

            /*
             * Jika akun belum tersedia, buat akun baru.
             */
            if ($user === null) {
                User::create([
                    'person_id' => $person->id,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'account_type' => 'internal',
                    'status' => 'active',
                    'failed_login_count' => 0,
                ]);

                return;
            }

            /*
             * Jika akun sebelumnya soft deleted,
             * aktifkan kembali.
             */
            if ($user->trashed()) {
                $user->restore();
            }

            /*
             * Perbarui akun administrator.
             *
             * Password sengaja diperbarui agar password lama
             * seperti "password" dari pengujian Tinker
             * tidak tetap digunakan.
             */
            $user->forceFill([
                'person_id' => $person->id,
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'account_type' => 'internal',
                'status' => 'active',
                'failed_login_count' => 0,
                'locked_until' => null,
            ])->save();
        });
    }
}
