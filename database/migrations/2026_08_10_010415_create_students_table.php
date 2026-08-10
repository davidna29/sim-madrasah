<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel siswa.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('person_id')
                ->constrained('people')
                ->restrictOnDelete();

            $table->foreignId('admission_academic_year_id')
                ->nullable()
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->string('student_number', 50)
                ->nullable()
                ->unique();

            $table->string('nisn', 50)
                ->nullable()
                ->unique();

            $table->string('registration_number', 50)
                ->nullable()
                ->unique();

            $table->date('admission_date')->nullable();
            $table->date('graduation_date')->nullable();

            $table->string('status', 30)->default('active');

            $table->string('previous_school', 150)->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('person_id');

            $table->index('admission_academic_year_id');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel siswa.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
