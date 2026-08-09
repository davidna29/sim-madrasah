<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel role.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique();
            $table->string('display_name', 150);
            $table->text('description')->nullable();

            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel role.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
