<?php

namespace Tests\Feature\Admin;

use App\Models\GradeLevel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeLevelRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_level_can_be_created(): void
    {
        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'description' => 'Tingkat awal MTs.',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('grade_levels', [
            'id' => $gradeLevel->id,
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);
    }

    public function test_room_can_be_created(): void
    {
        $room = Room::create([
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'location' => 'Gedung Utama',
            'description' => 'Ruang praktik TIK.',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'is_active' => true,
        ]);
    }
}
