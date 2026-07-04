<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah kolom `jabatan_akademik` ke tabel obe_pengguna.
 *
 * KONSEP:
 *   - `role`             = peran AKTIF saat ini (kaprodi, kajur, dekan, wakil_dekan, dosen, ...)
 *   - `jabatan_akademik` = status dosen permanen, diisi 'dosen' bila user adalah dosen
 *                          yang sedang / pernah / akan menjabat struktural.
 *
 * Dengan cara ini:
 *   - Saat dosen diangkat jadi kaprodi → role = 'kaprodi', jabatan_akademik = 'dosen'
 *   - Saat jabatan berakhir dan diganti → role dikembalikan ke 'dosen' dari jabatan_akademik
 *   - Semua query "siapa saja dosen?" cukup cek: role = 'dosen' OR jabatan_akademik = 'dosen'
 *
 * Data lama dibackfill otomatis: siapa pun yang role-nya bukan dosen murni tapi
 * punya DosenProfile (obe_dosen) → jabatan_akademik diset 'dosen'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obe_pengguna', function (Blueprint $table) {
            // NULL  = bukan dosen (mahasiswa, admin, tendik, dsb.)
            // 'dosen' = adalah dosen, meski saat ini menjabat struktural
            $table->string('jabatan_akademik')->nullable()->after('role')
                ->comment('Null = bukan dosen. "dosen" = dosen yg sedang/pernah menjabat struktural.');
        });

        // Backfill: role yang sudah "dosen murni" → jabatan_akademik = 'dosen'
        DB::statement("
            UPDATE obe_pengguna
            SET jabatan_akademik = 'dosen'
            WHERE role = 'dosen'
        ");

        // Backfill: petinggi yang punya DosenProfile (obe_dosen) → juga dosen
        DB::statement("
            UPDATE obe_pengguna p
            INNER JOIN obe_dosen d ON d.user_id = p.id
            SET p.jabatan_akademik = 'dosen'
            WHERE p.role IN ('kaprodi', 'kajur', 'dekan', 'wakil_dekan')
              AND p.jabatan_akademik IS NULL
        ");

        // Backfill: kaprodi yang punya KaprodiProfile tapi belum ditandai (edge case)
        // Kaprodi di sistem ini secara akademik juga adalah dosen
        DB::statement("
            UPDATE obe_pengguna p
            INNER JOIN obe_kaprodi k ON k.user_id = p.id
            SET p.jabatan_akademik = 'dosen'
            WHERE p.role = 'kaprodi'
              AND p.jabatan_akademik IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('obe_pengguna', function (Blueprint $table) {
            $table->dropColumn('jabatan_akademik');
        });
    }
};