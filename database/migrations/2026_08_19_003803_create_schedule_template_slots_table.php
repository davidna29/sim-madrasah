<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel slot pada template jadwal.
     */
    public function up(): void
    {
        Schema::create('schedule_template_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('schedule_template_id')
                ->constrained('schedule_templates')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('sort_order');

            $table->time('starts_at');
            $table->time('ends_at');

            $table->string('slot_type', 30)->default('kbm');
            $table->string('label', 100)->nullable();

            $table->boolean('is_teaching_slot')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['schedule_template_id', 'day_of_week', 'sort_order'],
                'schedule_template_day_sort_unique'
            );

            $table->index('day_of_week');
            $table->index('slot_type');
            $table->index('is_teaching_slot');
        });
    }

    /**
     * Menghapus tabel slot pada template jadwal.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_template_slots');
    }
};
// day_of_week:
// 1 = Senin
// 2 = Selasa
// 3 = Rabu
// 4 = Kamis
// 5 = Jumat
// 6 = Sabtu
// 7 = Minggu
