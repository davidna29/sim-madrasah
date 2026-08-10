<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel orang tua atau wali siswa.
     */
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('person_id')
                ->constrained('people')
                ->restrictOnDelete();

            $table->string('relationship', 30);
            $table->string('occupation', 100)->nullable();
            $table->string('education_level', 50)->nullable();
            $table->string('income_range', 50)->nullable();

            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_financial_responsible')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'person_id']);

            $table->index('relationship');
            $table->index('is_primary_contact');
            $table->index('is_emergency_contact');
            $table->index('is_financial_responsible');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel orang tua atau wali siswa.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};
