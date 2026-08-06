<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kebutuhan akun SIM Madrasah
     * ke tabel users bawaan Laravel Breeze.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('person_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('people')
                ->restrictOnDelete();

            /*
             * Username dibuat nullable sementara agar instalasi
             * Breeze dan data lama tetap kompatibel.
             */
            $table->string('username', 100)
                ->nullable()
                ->unique()
                ->after('name');

            $table->string('account_type', 30)
                ->default('internal')
                ->after('password');

            $table->string('status', 30)
                ->default('active')
                ->after('account_type');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('email_verified_at');

            $table->string('last_login_ip', 45)
                ->nullable()
                ->after('last_login_at');

            $table->timestamp('password_changed_at')
                ->nullable()
                ->after('last_login_ip');

            $table->unsignedSmallInteger('failed_login_count')
                ->default(0)
                ->after('password_changed_at');

            $table->timestamp('locked_until')
                ->nullable()
                ->after('failed_login_count');

            $table->softDeletes();

            $table->index(
                ['status', 'account_type'],
                'users_status_account_type_index'
            );
        });
    }

    /**
     * Mengembalikan struktur users ke kondisi sebelumnya.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_account_type_index');

            $table->dropConstrainedForeignId('person_id');

            $table->dropColumn([
                'username',
                'account_type',
                'status',
                'last_login_at',
                'last_login_ip',
                'password_changed_at',
                'failed_login_count',
                'locked_until',
                'deleted_at',
            ]);
        });
    }
};
