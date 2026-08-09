<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel mata pelajaran.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 150);

            $table->string('subject_group', 50)->default('general');

            $table->boolean('is_local_content')->default(false);
            $table->boolean('is_religious')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('subject_group');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel mata pelajaran.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
