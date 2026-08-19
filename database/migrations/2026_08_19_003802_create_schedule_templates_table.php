<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel template jadwal pelajaran.
     */
    public function up(): void
    {
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->json('active_days')->nullable();
            $table->json('holiday_days')->nullable();

            $table->unsignedTinyInteger('max_slots_per_day')->default(10);
            $table->unsignedSmallInteger('standard_slot_duration_minutes')->default(35);

            $table->string('status', 30)->default('draft');
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('is_active');
            $table->index('created_by');
        });
    }

    /**
     * Menghapus tabel template jadwal pelajaran.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_templates');
    }
};
