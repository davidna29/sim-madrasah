<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel plotting beban mengajar.
     */
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('class_group_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('subject_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('teacher_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('weekly_hours');

            $table->string('status', 30)->default('active');
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                [
                    'academic_year_id',
                    'semester_id',
                    'class_group_id',
                    'subject_id',
                    'teacher_user_id',
                ],
                'teaching_assignments_unique_context'
            );

            $table->index('academic_year_id');
            $table->index('semester_id');
            $table->index('class_group_id');
            $table->index('subject_id');
            $table->index('teacher_user_id');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel plotting beban mengajar.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
