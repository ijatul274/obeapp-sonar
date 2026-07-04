<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Hitung ketercapaian CPL.
 *
 * Rumus (sementara, bisa disesuaikan):
 *   - Per kelas: untuk tiap CPL, ambil seluruh nilai CPMK (per mahasiswa × CPMK)
 *     yang ter-mapping ke CPL tersebut, lalu rata-rata sederhana → persen.
 *   - Per mahasiswa (akumulatif): untuk tiap CPL, ambil semua nilai CPMK
 *     mahasiswa di seluruh kelas yang ter-mapping ke CPL itu, lalu rata-rata.
 *
 * CPMK yang belum dinilai (score null) di-skip, tidak dihitung sebagai 0.
 */
class CplAchievementService
{
    /**
     * Untuk halaman kaprodi/dosen laporan per kelas.
     *
     * @param  array       $rows   hasil buildRows() — tiap row punya 'cpmks' => [['id','code','total'(score), ...]]
     * @param  Collection  $cpmks  collection CPMK kelas (Cpmk atau ClassroomCpmk) dengan relasi cpl.
     * @return array<int, array{cpl: object, average: float|null, sample_count: int}>
     *         keyed by cpl_id, urut sesuai cpl->code.
     */
    public static function perClassroom($rows, $cpmks): array
    {
        // Index cpmk_id => CPL (object)
        $cpmkToCpl = [];
        foreach ($cpmks as $cpmk) {
            if ($cpmk->cpl) {
                $cpmkToCpl[$cpmk->id] = $cpmk->cpl;
            }
        }

        // Kumpulkan skor: cpl_id => [scores...]
        $bucket = [];
        foreach ($rows as $row) {
            foreach ($row['cpmks'] as $cR) {
                $cpl = $cpmkToCpl[$cR['id']] ?? null;
                if (!$cpl) continue;
                $score = $cR['total'] ?? $cR['score'] ?? null;
                if ($score === null) continue;
                $bucket[$cpl->id]['cpl']      = $cpl;
                $bucket[$cpl->id]['scores'][] = (float) $score;
            }
        }

        // Build hasil
        $result = [];
        foreach ($bucket as $cplId => $b) {
            $scores = $b['scores'] ?? [];
            $cpl    = $b['cpl'];
            $result[$cplId] = [
                'cpl'           => $cpl,
                'average'       => count($scores) ? round(array_sum($scores) / count($scores), 1) : null,
                'sample_count'  => count($scores),
                'support_count' => null,
                'taken_count'   => count($scores),
                'min_target'    => (float) ($cpl->min_target ?? 70),
            ];
        }

        // Urutkan berdasarkan kode CPL
        uasort($result, fn($a, $b) => strcmp($a['cpl']->code, $b['cpl']->code));

        return $result;
    }
}
