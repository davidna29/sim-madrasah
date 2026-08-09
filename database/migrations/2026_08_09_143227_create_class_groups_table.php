<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel rombongan belajar.
     */
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('grade_level_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('homeroom_teacher_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('parallel_name', 30)->nullable();

            $table->unsignedSmallInteger('capacity')->nullable();

            $table->string('status', 30)->default('active');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['academic_year_id', 'code']);

            $table->index('grade_level_id');
            $table->index('room_id');
            $table->index('homeroom_teacher_user_id');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel rombongan belajar.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
