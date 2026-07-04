<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $map = [
        'users'                            => 'obe_pengguna',
        'graduate_profiles'                => 'obe_profil_lulusan',
        'cpls'                             => 'obe_cpl',
        'courses'                          => 'obe_mata_kuliah',
        'course_cpl'                       => 'obe_mata_kuliah_cpl',
        'course_user'                      => 'obe_mata_kuliah_pengguna',
        'cpmks'                            => 'obe_cpmk',
        'indicators'                       => 'obe_indikator',
        'classrooms'                       => 'obe_kelas',
        'classroom_user'                   => 'obe_kelas_pengguna',
        'classroom_cpmk_lecturer'          => 'obe_kelas_cpmk_dosen',
        'classroom_cpmks'                  => 'obe_kelas_cpmk',
        'classroom_cpmk_indicators'        => 'obe_kelas_cpmk_indikator',
        'classroom_cpmk_assessments'       => 'obe_kelas_cpmk_komponen',
        'classroom_cpmk_assessment_scores' => 'obe_kelas_cpmk_nilai',
        'assessments'                      => 'obe_komponen_penilaian',
        'assessment_scores'                => 'obe_nilai_komponen',
        'settings'                         => 'obe_pengaturan',
        'notifications'                    => 'obe_notifikasi',
        'login_slides'                     => 'obe_carousel_login',
    ];

    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->map as $old => $new) {
            if (Schema::hasTable($old) && !Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (array_reverse($this->map, true) as $old => $new) {
            if (Schema::hasTable($new) && !Schema::hasTable($old)) {
                Schema::rename($new, $old);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
