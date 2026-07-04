<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom program_studi_id ke obe_cpl dan obe_profil_lulusan.
 *
 * Tujuan: Memisahkan data CPL dan Profil Lulusan per prodi, sehingga
 * setiap kaprodi hanya melihat dan mengelola data milik prodinya sendiri.
 *
 * Backward-compat: kolom nullable → data lama tidak rusak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_cpl', function (Blueprint $table) {
            $table->unsignedBigInteger('program_studi_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('program_studi_id')
                  ->references('id')
                  ->on('obe_program_studi')
                  ->nullOnDelete();
        });

        Schema::table('obe_profil_lulusan', function (Blueprint $table) {
            $table->unsignedBigInteger('program_studi_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('program_studi_id')
                  ->references('id')
                  ->on('obe_program_studi')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('obe_cpl', function (Blueprint $table) {
            $table->dropForeign(['program_studi_id']);
            $table->dropColumn('program_studi_id');
        });

        Schema::table('obe_profil_lulusan', function (Blueprint $table) {
            $table->dropForeign(['program_studi_id']);
            $table->dropColumn('program_studi_id');
        });
    }
};