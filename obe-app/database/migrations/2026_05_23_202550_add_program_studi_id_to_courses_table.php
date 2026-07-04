<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom program_studi_id ke obe_mata_kuliah.
 *
 * Tujuan: Memungkinkan scope kaprodi berbasis prodi (bukan hanya jurusan),
 * sehingga dua kaprodi dalam satu jurusan tidak lagi melihat data yang sama.
 *
 * Backward-compat: kolom nullable → data lama tidak rusak.
 * Isi otomatis: jika jurusan hanya punya 1 prodi, otomatis diisi (lihat seeder/after).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_mata_kuliah', function (Blueprint $table) {
            $table->unsignedBigInteger('program_studi_id')
                  ->nullable()
                  ->after('jurusan_id');

            $table->foreign('program_studi_id')
                  ->references('id')
                  ->on('obe_program_studi')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('obe_mata_kuliah', function (Blueprint $table) {
            $table->dropForeign(['program_studi_id']);
            $table->dropColumn('program_studi_id');
        });
    }
};