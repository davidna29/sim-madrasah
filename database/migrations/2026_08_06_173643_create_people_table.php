<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel identitas umum seseorang.
     */
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();

            $table->string('national_id_number', 32)
                ->nullable()
                ->unique();

            $table->string('full_name', 150);

            $table->string('birth_place', 100)
                ->nullable();

            $table->date('birth_date')
                ->nullable();

            $table->string('gender', 20)
                ->nullable();

            $table->string('religion', 30)
                ->nullable();

            $table->string('email', 191)
                ->nullable();

            $table->string('phone', 30)
                ->nullable();

            $table->text('address')
                ->nullable();

            /*
             * Foreign key ke file akan dibuat setelah
             * tabel pengelolaan file tersedia.
             */
            $table->unsignedBigInteger('photo_file_id')
                ->nullable();

            $table->timestamps();

            $table->index('full_name');
            $table->index('phone');
        });
    }

    /**
     * Menghapus tabel people saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
