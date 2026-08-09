<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat permission langsung untuk user.
     *
     * Digunakan hanya untuk pengecualian khusus.
     */
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('permission_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('permission_mode', 20)->default('allow');

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->primary([
                'user_id',
                'permission_id',
            ]);

            $table->index('permission_mode');
            $table->index('expires_at');
        });
    }

    /**
     * Menghapus permission langsung user.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
