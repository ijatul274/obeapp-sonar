<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill program_studi_id pada mata kuliah lama yang belum memilikinya.
 *
 * Logika:
 *  - Jika jurusan hanya punya 1 prodi → otomatis diisi
 *  - Jika jurusan punya >1 prodi → dibiarkan NULL (admin/kaprodi set manual via UI)
 *
 * Migration ini DATA-ONLY (tidak ubah skema), sehingga aman di-rollback
 * dengan menset ulang ke NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ambil semua jurusan beserta jumlah prodinya
        $jurusans = DB::table('obe_jurusan')->get();

        foreach ($jurusans as $jurusan) {
            $prodis = DB::table('obe_program_studi')
                ->where('jurusan_id', $jurusan->id)
                ->get();

            // Hanya auto-fill jika jurusan punya tepat 1 prodi (tidak ambigu)
            if ($prodis->count() === 1) {
                $prodiId = $prodis->first()->id;

                // Update mata kuliah dari jurusan ini yang belum punya program_studi_id
                DB::table('obe_mata_kuliah')
                    ->where('jurusan_id', $jurusan->id)
                    ->whereNull('program_studi_id')
                    ->update(['program_studi_id' => $prodiId]);
            }
        }
    }

    public function down(): void
    {
        // Rollback: bersihkan program_studi_id yang di-set oleh migration ini
        // (tidak mungkin tahu persis mana yang di-set manual vs otomatis,
        //  jadi kita hanya reset ke NULL untuk jurusan 1-prodi)
        $jurusans = DB::table('obe_jurusan')->get();

        foreach ($jurusans as $jurusan) {
            $prodis = DB::table('obe_program_studi')
                ->where('jurusan_id', $jurusan->id)
                ->get();

            if ($prodis->count() === 1) {
                $prodiId = $prodis->first()->id;

                DB::table('obe_mata_kuliah')
                    ->where('jurusan_id', $jurusan->id)
                    ->where('program_studi_id', $prodiId)
                    ->update(['program_studi_id' => null]);
            }
        }
    }
};