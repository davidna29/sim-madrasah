<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel riwayat kelas siswa.
     */
    public function up(): void
    {
        Schema::create('student_class_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('class_group_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('status', 30)->default('active');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->boolean('is_current')->default(false);

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'semester_id']);

            $table->index('academic_year_id');
            $table->index('semester_id');
            $table->index('class_group_id');
            $table->index('status');
            $table->index('is_current');
        });
    }

    /**
     * Menghapus tabel riwayat kelas siswa.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_histories');
    }
};
