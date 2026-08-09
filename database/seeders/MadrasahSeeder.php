<?php

namespace Database\Seeders;

use App\Models\Madrasah;
use Illuminate\Database\Seeder;

class MadrasahSeeder extends Seeder
{
    /**
     * Membuat data identitas madrasah awal.
     */
    public function run(): void
    {
        Madrasah::query()->updateOrCreate(
            [
                'code' => 'default',
            ],
            [
                'name' => 'SIM Madrasah',
                'nsm' => null,
                'npsn' => null,
                'email' => null,
                'phone' => null,
                'address' => null,
                'village' => null,
                'district' => null,
                'city' => null,
                'province' => null,
                'postal_code' => null,
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );
    }
}
