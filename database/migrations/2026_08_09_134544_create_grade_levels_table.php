<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel tingkat kelas.
     */
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->unsignedTinyInteger('level_number');

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('level_number');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel tingkat kelas.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};
