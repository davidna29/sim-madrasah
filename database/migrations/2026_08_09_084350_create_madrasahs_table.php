<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel identitas madrasah.
     */
    public function up(): void
    {
        Schema::create('madrasahs', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 150);

            $table->string('nsm', 30)
                ->nullable()
                ->unique();

            $table->string('npsn', 30)
                ->nullable()
                ->unique();

            $table->string('email', 191)
                ->nullable();

            $table->string('phone', 30)
                ->nullable();

            $table->text('address')
                ->nullable();

            $table->string('village', 100)
                ->nullable();

            $table->string('district', 100)
                ->nullable();

            $table->string('city', 100)
                ->nullable();

            $table->string('province', 100)
                ->nullable();

            $table->string('postal_code', 10)
                ->nullable();

            /*
             * Foreign key ke tabel files belum dibuat karena
             * modul file management belum tersedia.
             */
            $table->unsignedBigInteger('logo_file_id')
                ->nullable();

            $table->string('timezone', 50)
                ->default('Asia/Jakarta');

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel madrasahs.
     */
    public function down(): void
    {
        Schema::dropIfExists('madrasahs');
    }
};
