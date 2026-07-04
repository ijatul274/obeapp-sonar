<?php

namespace App\Services;

class GradeService
{
    /**
     * Ambang minimum CPMK dinyatakan lulus (skala 0–100)
     */
    const MIN_CPMK_PASS = 70.0;

    /**
     * Cek apakah satu nilai CPMK lulus
     */
    public static function cpmkLulus(float $score): bool
    {
        return $score >= self::MIN_CPMK_PASS;
    }

    /**
     * Hitung nilai akhir mata kuliah (0–100).
     * Jika ada 1+ CPMK yang tidak lulus → otomatis 0 (nilai E).
     *
     * @param array $cpmkScores  [['score'=>float, 'weight'=>float], ...]
     *                            weight dalam persen (0–100), total harus 100
     */
    public static function nilaiAkhirMataKuliah(array $cpmkScores): float
    {
        // Jika ada CPMK yang gagal, nilai akhir = 0 (E)
        foreach ($cpmkScores as $item) {
            if (!self::cpmkLulus((float) $item['score'])) {
                return 0.0;
            }
        }

        // Semua lulus → hitung weighted sum
        $total = 0.0;
        foreach ($cpmkScores as $item) {
            $total += (float) $item['score'] * ((float) $item['weight'] / 100);
        }

        return round($total, 2);
    }

    /**
     * Konversi nilai 0–100 ke mutu (0.0–4.0) mengikuti SATU UNRI
     */
    public static function toMutu(float $score): float
    {
        if ($score >= 85) return 4.00;
        if ($score >= 80) return 3.75;
        if ($score >= 75) return 3.50;
        if ($score >= 70) return 3.00;
        if ($score >= 65) return 2.75;
        if ($score >= 60) return 2.50;
        if ($score >= 55) return 2.00;
        if ($score >= 45) return 1.00;
        return 0.00;
    }

    /**
     * Konversi nilai 0–100 ke huruf (A–E) mengikuti SATU UNRI
     */
    public static function toHuruf(float $score): string
    {
        if ($score >= 85) return 'A';
        if ($score >= 80) return 'A-';
        if ($score >= 75) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 65) return 'B-';
        if ($score >= 60) return 'C+';
        if ($score >= 55) return 'C';
        if ($score >= 45) return 'D';
        return 'E';
    }

    /**
     * Konversi lengkap: nilai → ['huruf'=>, 'mutu'=>, 'lulus'=>]
     */
    public static function toKonvensional(float $score): array
    {
        return [
            'huruf' => self::toHuruf($score),
            'mutu'  => self::toMutu($score),
            'lulus' => $score >= 55,  // minimal C untuk dianggap lulus konvensional
        ];
    }

    /**
     * Hitung IPK (rata-rata mutu berbobot SKS)
     *
     * @param array $rows  [['mutu'=>float, 'sks'=>int], ...]
     */
    public static function hitungIpk(array $rows): float
    {
        $totalSks  = 0;
        $totalMutu = 0.0;

        foreach ($rows as $r) {
            $sks = (int) ($r['sks'] ?? 0);
            $totalSks  += $sks;
            $totalMutu += (float) $r['mutu'] * $sks;
        }

        if ($totalSks === 0) return 0.0;

        return round($totalMutu / $totalSks, 2);
    }
}
