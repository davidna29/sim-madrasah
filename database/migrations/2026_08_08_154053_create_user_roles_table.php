<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel penghubung user dan role.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->primary([
                'user_id',
                'role_id',
            ]);

            $table->index('expires_at');
        });
    }

    /**
     * Menghapus tabel penghubung user dan role.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};