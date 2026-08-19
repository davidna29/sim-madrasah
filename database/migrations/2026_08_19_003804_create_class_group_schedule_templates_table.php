<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel assignment rombel ke template jadwal.
     */
    public function up(): void
    {
        Schema::create('class_group_schedule_templates', function (Blueprint $table) {
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

            $table->foreignId('schedule_template_id')
                ->constrained('schedule_templates')
                ->restrictOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamp('assigned_at')->nullable();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'semester_id', 'class_group_id'],
                'class_group_schedule_template_unique'
            );

            $table->index('schedule_template_id');
            $table->index('is_active');
            $table->index('assigned_by');
        });
    }

    /**
     * Menghapus tabel assignment rombel ke template jadwal.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_group_schedule_templates');
    }
};
