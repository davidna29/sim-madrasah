<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Membuat data ruangan awal.
     */
    public function run(): void
    {
        $rooms = [
            [
                'code' => 'R-VII-A',
                'name' => 'Ruang VII A',
                'room_type' => 'classroom',
                'capacity' => 32,
            ],
            [
                'code' => 'R-VIII-A',
                'name' => 'Ruang VIII A',
                'room_type' => 'classroom',
                'capacity' => 32,
            ],
            [
                'code' => 'R-IX-A',
                'name' => 'Ruang IX A',
                'room_type' => 'classroom',
                'capacity' => 32,
            ],
            [
                'code' => 'LAB-KOM',
                'name' => 'Laboratorium Komputer',
                'room_type' => 'laboratory',
                'capacity' => 30,
            ],
        ];

        foreach ($rooms as $room) {
            Room::query()->updateOrCreate(
                [
                    'code' => $room['code'],
                ],
                [
                    'name' => $room['name'],
                    'room_type' => $room['room_type'],
                    'capacity' => $room['capacity'],
                    'location' => null,
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
