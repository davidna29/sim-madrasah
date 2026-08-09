<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel permission.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150)->unique();
            $table->string('module', 100);
            $table->string('action', 50);
            $table->string('display_name', 150);
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('module');
            $table->index('action');
            $table->index(['module', 'action']);
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel permission.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
