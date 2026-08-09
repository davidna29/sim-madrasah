<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel guru dan pegawai.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('person_id')
                ->constrained('people')
                ->restrictOnDelete();

            $table->string('employee_number', 50)
                ->nullable()
                ->unique();

            $table->string('nip', 50)
                ->nullable()
                ->unique();

            $table->string('nuptk', 50)
                ->nullable()
                ->unique();

            $table->string('employee_type', 30);
            $table->string('employment_status', 50)->nullable();
            $table->string('position', 100)->nullable();

            $table->date('join_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('education_level', 50)->nullable();
            $table->string('major', 100)->nullable();

            $table->boolean('is_teacher')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('person_id');

            $table->index('employee_type');
            $table->index('employment_status');
            $table->index('position');
            $table->index('is_teacher');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel guru dan pegawai.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
