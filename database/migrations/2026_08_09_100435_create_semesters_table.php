<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel semester.
     */
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('code', 30);
            $table->string('name', 50);

            $table->string('semester_type', 20);

            $table->date('start_date');
            $table->date('end_date');

            $table->string('status', 30)->default('draft');

            $table->boolean('is_active')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->timestamp('locked_at')->nullable();

            $table->foreignId('locked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['academic_year_id', 'semester_type']);
            $table->unique(['academic_year_id', 'code']);

            $table->index('semester_type');
            $table->index('status');
            $table->index('is_active');
            $table->index('is_locked');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Menghapus tabel semester.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
