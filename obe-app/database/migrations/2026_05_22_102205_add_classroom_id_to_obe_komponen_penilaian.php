<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah FK classroom_id ke obe_komponen_penilaian.
 *
 * FK tidak bisa ditambah di create_assessments_table karena tabel classrooms/obe_kelas
 * belum ada saat migration itu berjalan. FK ditambah di sini setelah rename + classrooms ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_komponen_penilaian', function (Blueprint $table) {
            $table->foreign('classroom_id')
                ->references('id')
                ->on('obe_kelas')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('obe_komponen_penilaian', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
        });
    }
};