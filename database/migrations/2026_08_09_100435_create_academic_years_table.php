<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel tahun ajaran.
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();
            $table->string('name', 30);

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

            $table->index('status');
            $table->index('is_active');
            $table->index('is_locked');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Menghapus tabel tahun ajaran.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
