<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel ketersediaan guru.
     */
    public function up(): void
    {
        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('teacher_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('day_of_week');

            $table->time('starts_at');
            $table->time('ends_at');

            $table->string('availability_type', 30)->default('unavailable');
            $table->string('reason', 150)->nullable();
            $table->text('notes')->nullable();

            $table->string('status', 30)->default('active');
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                [
                    'academic_year_id',
                    'semester_id',
                    'teacher_user_id',
                    'day_of_week',
                    'starts_at',
                    'ends_at',
                    'availability_type',
                    'is_active',
                ],
                'teacher_availabilities_unique_active_context'
            );

            $table->index('academic_year_id');
            $table->index('semester_id');
            $table->index('teacher_user_id');
            $table->index('day_of_week');
            $table->index('availability_type');
            $table->index('status');
            $table->index('is_active');
            $table->index('created_by');
        });
    }

    /**
     * Menghapus tabel ketersediaan guru.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_availabilities');
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
