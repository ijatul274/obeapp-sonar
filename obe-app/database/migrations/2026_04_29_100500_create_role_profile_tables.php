<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel profil per-role sebagai pelengkap obe_pengguna (auth tunggal).
 * Multi-guard tidak digunakan; setiap row adalah profil tambahan ber-FK
 * ke obe_pengguna.id, sehingga session/authentication tetap satu jalur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obe_kaprodi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('obe_pengguna')->cascadeOnDelete();
            $table->string('nip')->nullable();
            $table->string('singkatan', 30)->nullable();
            $table->foreignId('program_studi_id')->nullable()->constrained('obe_program_studi')->nullOnDelete();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('obe_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('obe_pengguna')->cascadeOnDelete();
            $table->string('nip')->nullable();
            $table->string('singkatan', 30)->nullable();
            $table->foreignId('program_studi_id')->nullable()->constrained('obe_program_studi')->nullOnDelete();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('obe_pj_lab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('obe_pengguna')->cascadeOnDelete();
            $table->string('nip')->nullable();
            $table->string('singkatan', 30)->nullable();
            $table->string('nama_lab')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('obe_tendik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('obe_pengguna')->cascadeOnDelete();
            $table->string('nip')->nullable();
            $table->string('singkatan', 30)->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('obe_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('obe_pengguna')->cascadeOnDelete();
            $table->string('nim')->nullable();
            $table->foreignId('program_studi_id')->nullable()->constrained('obe_program_studi')->nullOnDelete();
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obe_mahasiswa');
        Schema::dropIfExists('obe_tendik');
        Schema::dropIfExists('obe_pj_lab');
        Schema::dropIfExists('obe_dosen');
        Schema::dropIfExists('obe_kaprodi');
    }
};
