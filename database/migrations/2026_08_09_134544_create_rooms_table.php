<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel ruangan.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 100);

            $table->string('room_type', 50)->default('classroom');
            $table->unsignedSmallInteger('capacity')->nullable();

            $table->string('location', 150)->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('room_type');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel ruangan.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
