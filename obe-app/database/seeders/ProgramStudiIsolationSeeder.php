<?php

namespace Database\Seeders;

use App\Models\Cpl;
use App\Models\GraduateProfile;
use App\Models\Jurusan;
use App\Models\KaprodiProfile;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk memisahkan data per Program Studi.
 *
 * Membuat:
 *  - 3 Program Studi: S1 Teknik Informatika, S1 Teknik Elektro, D3 Teknik Elektro
 *  - 3 akun Kaprodi masing-masing terikat ke prodinya
 *  - CPL & Profil Lulusan yang berbeda untuk setiap prodi
 *
 * Jalankan: php artisan db:seed --class=ProgramStudiIsolationSeeder
 */
class ProgramStudiIsolationSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Jurusan ────────────────────────────────────────────────────
        $jurusanTI = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Teknik Informatika'],
            ['kode' => 'TI']
        );

        $jurusanTE = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Teknik Elektro'],
            ['kode' => 'TE']
        );

        // ── 2. Program Studi ───────────────────────────────────────────────
        $prodiS1TI = ProgramStudi::firstOrCreate(
            ['kode' => 'S1-TI'],
            [
                'nama_prodi' => 'S1 Teknik Informatika',
                'jurusan_id' => $jurusanTI->id,
                'visi'       => 'Menjadi program studi informatika unggul di tingkat nasional.',
            ]
        );

        $prodiS1TE = ProgramStudi::firstOrCreate(
            ['kode' => 'S1-TE'],
            [
                'nama_prodi' => 'S1 Teknik Elektro',
                'jurusan_id' => $jurusanTE->id,
                'visi'       => 'Menjadi program studi teknik elektro unggul di tingkat nasional.',
            ]
        );

        $prodiD3TE = ProgramStudi::firstOrCreate(
            ['kode' => 'D3-TE'],
            [
                'nama_prodi' => 'D3 Teknik Elektro',
                'jurusan_id' => $jurusanTE->id,
                'visi'       => 'Menghasilkan ahli madya teknik elektro yang kompeten dan siap kerja.',
            ]
        );

        // ── 3. Akun Kaprodi ───────────────────────────────────────────────
        $kaprodiS1TI = User::firstOrCreate(
            ['email' => 'kaprodi.s1ti@example.com'],
            [
                'name'              => 'Kaprodi S1 Teknik Informatika',
                'identity'          => '199001001',
                'initials'          => 'KTI',
                'password'          => Hash::make('password'),
                'role'              => 'kaprodi',
                'jabatan_akademik'  => 'dosen',
                'jurusan_id'        => $jurusanTI->id,
            ]
        );

        KaprodiProfile::updateOrCreate(
            ['user_id' => $kaprodiS1TI->id],
            [
                'nip'              => '199001001',
                'singkatan'        => 'KTI',
                'program_studi_id' => $prodiS1TI->id,
            ]
        );

        $kaprodiS1TE = User::firstOrCreate(
            ['email' => 'kaprodi.s1te@example.com'],
            [
                'name'              => 'Kaprodi S1 Teknik Elektro',
                'identity'          => '199001002',
                'initials'          => 'KTE',
                'password'          => Hash::make('password'),
                'role'              => 'kaprodi',
                'jabatan_akademik'  => 'dosen',
                'jurusan_id'        => $jurusanTE->id,
            ]
        );

        KaprodiProfile::updateOrCreate(
            ['user_id' => $kaprodiS1TE->id],
            [
                'nip'              => '199001002',
                'singkatan'        => 'KTE',
                'program_studi_id' => $prodiS1TE->id,
            ]
        );

        $kaprodiD3TE = User::firstOrCreate(
            ['email' => 'kaprodi.d3te@example.com'],
            [
                'name'              => 'Kaprodi D3 Teknik Elektro',
                'identity'          => '199001003',
                'initials'          => 'KD3',
                'password'          => Hash::make('password'),
                'role'              => 'kaprodi',
                'jabatan_akademik'  => 'dosen',
                'jurusan_id'        => $jurusanTE->id,
            ]
        );

        KaprodiProfile::updateOrCreate(
            ['user_id' => $kaprodiD3TE->id],
            [
                'nip'              => '199001003',
                'singkatan'        => 'KD3',
                'program_studi_id' => $prodiD3TE->id,
            ]
        );

        // ── 4. CPL per Prodi ──────────────────────────────────────────────

        // CPL S1 Teknik Informatika
        $cplS1TI = [
            ['code' => 'CPL-TI-1', 'description' => 'Mampu menerapkan pengetahuan matematika, sains, dan rekayasa perangkat lunak.', 'min_target' => 75],
            ['code' => 'CPL-TI-2', 'description' => 'Mampu merancang dan mengimplementasikan sistem berbasis komputer.', 'min_target' => 70],
            ['code' => 'CPL-TI-3', 'description' => 'Mampu menganalisis dan menyelesaikan masalah rekayasa informatika.', 'min_target' => 70],
            ['code' => 'CPL-TI-4', 'description' => 'Mampu bekerja dalam tim multidisiplin secara profesional.', 'min_target' => 65],
        ];

        foreach ($cplS1TI as $data) {
            Cpl::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['program_studi_id' => $prodiS1TI->id])
            );
        }

        // CPL S1 Teknik Elektro
        $cplS1TE = [
            ['code' => 'CPL-TE-1', 'description' => 'Mampu menerapkan pengetahuan matematika dan fisika dalam rekayasa elektro.', 'min_target' => 75],
            ['code' => 'CPL-TE-2', 'description' => 'Mampu merancang sistem tenaga, elektronika, dan kendali.', 'min_target' => 70],
            ['code' => 'CPL-TE-3', 'description' => 'Mampu mengoperasikan dan memelihara sistem ketenagalistrikan.', 'min_target' => 70],
            ['code' => 'CPL-TE-4', 'description' => 'Mampu memahami tanggung jawab profesional dan etika keteknikan.', 'min_target' => 65],
        ];

        foreach ($cplS1TE as $data) {
            Cpl::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['program_studi_id' => $prodiS1TE->id])
            );
        }

        // CPL D3 Teknik Elektro
        $cplD3TE = [
            ['code' => 'CPL-D3TE-1', 'description' => 'Mampu mengaplikasikan konsep dasar elektro dalam pekerjaan teknis.', 'min_target' => 70],
            ['code' => 'CPL-D3TE-2', 'description' => 'Mampu mengoperasikan peralatan dan instalasi listrik dengan benar.', 'min_target' => 70],
            ['code' => 'CPL-D3TE-3', 'description' => 'Mampu mendiagnosis dan memperbaiki gangguan sistem elektronika.', 'min_target' => 65],
        ];

        foreach ($cplD3TE as $data) {
            Cpl::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['program_studi_id' => $prodiD3TE->id])
            );
        }

        // ── 5. Profil Lulusan per Prodi ────────────────────────────────────

        // Profil Lulusan S1 TI
        $plS1TI = [
            ['name' => 'Software Engineer', 'description' => 'Lulusan mampu bekerja sebagai pengembang perangkat lunak profesional.'],
            ['name' => 'Data Scientist',    'description' => 'Lulusan mampu mengolah dan menganalisis data skala besar.'],
            ['name' => 'Wirausahawan TI',   'description' => 'Lulusan mampu mendirikan dan mengelola usaha berbasis teknologi informasi.'],
        ];

        foreach ($plS1TI as $data) {
            GraduateProfile::firstOrCreate(
                ['name' => $data['name'], 'program_studi_id' => $prodiS1TI->id],
                array_merge($data, ['program_studi_id' => $prodiS1TI->id])
            );
        }

        // Profil Lulusan S1 TE
        $plS1TE = [
            ['name' => 'Insinyur Tenaga Listrik',  'description' => 'Lulusan mampu merancang dan mengelola sistem ketenagalistrikan.'],
            ['name' => 'Insinyur Elektronika',     'description' => 'Lulusan mampu merancang rangkaian dan sistem elektronika.'],
            ['name' => 'Insinyur Kontrol & Otomasi', 'description' => 'Lulusan mampu mengembangkan sistem kendali otomatis industri.'],
        ];

        foreach ($plS1TE as $data) {
            GraduateProfile::firstOrCreate(
                ['name' => $data['name'], 'program_studi_id' => $prodiS1TE->id],
                array_merge($data, ['program_studi_id' => $prodiS1TE->id])
            );
        }

        // Profil Lulusan D3 TE
        $plD3TE = [
            ['name' => 'Teknisi Instalasi Listrik', 'description' => 'Lulusan mampu melaksanakan instalasi dan pemeliharaan sistem listrik.'],
            ['name' => 'Teknisi Elektronika',       'description' => 'Lulusan mampu merakit, mengoperasikan, dan memperbaiki perangkat elektronika.'],
        ];

        foreach ($plD3TE as $data) {
            GraduateProfile::firstOrCreate(
                ['name' => $data['name'], 'program_studi_id' => $prodiD3TE->id],
                array_merge($data, ['program_studi_id' => $prodiD3TE->id])
            );
        }

        $this->command->info('✅ Program Studi Isolation Seeder selesai!');
        $this->command->info('');
        $this->command->info('Akun Kaprodi yang dibuat:');
        $this->command->info('  kaprodi.s1ti@example.com  → S1 Teknik Informatika');
        $this->command->info('  kaprodi.s1te@example.com  → S1 Teknik Elektro');
        $this->command->info('  kaprodi.d3te@example.com  → D3 Teknik Elektro');
        $this->command->info('  Password semua: password');
    }
}