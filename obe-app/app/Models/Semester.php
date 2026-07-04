<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'obe_semester';

    protected $fillable = ['periode', 'tahun_ajaran', 'tanggal_mulai', 'tanggal_selesai'];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Default periode + tahun ajaran berdasarkan tanggal hari ini.
     * - Genap: 1 Feb – 31 Jul (tahun ajaran = (tahun-1)/tahun)
     * - Ganjil: 1 Agu – 31 Jan (tahun ajaran = tahun/(tahun+1))
     */
    public static function defaultForDate(Carbon $date): array
    {
        $month = (int) $date->month;
        $year  = (int) $date->year;

        // Februari (2) – Juli (7) → Genap
        if ($month >= 2 && $month <= 7) {
            return [
                'periode'         => 'genap',
                'tahun_ajaran'    => ($year - 1) . '/' . $year,
                'tanggal_mulai'   => Carbon::create($year, 2, 1)->toDateString(),
                'tanggal_selesai' => Carbon::create($year, 7, 31)->toDateString(),
            ];
        }

        // Agustus (8) – Januari (1) → Ganjil
        if ($month >= 8) {
            return [
                'periode'         => 'ganjil',
                'tahun_ajaran'    => $year . '/' . ($year + 1),
                'tanggal_mulai'   => Carbon::create($year, 8, 1)->toDateString(),
                'tanggal_selesai' => Carbon::create($year + 1, 1, 31)->toDateString(),
            ];
        }

        // Januari → masih semester Ganjil tahun ajaran sebelumnya
        return [
            'periode'         => 'ganjil',
            'tahun_ajaran'    => ($year - 1) . '/' . $year,
            'tanggal_mulai'   => Carbon::create($year - 1, 8, 1)->toDateString(),
            'tanggal_selesai' => Carbon::create($year, 1, 31)->toDateString(),
        ];
    }

    /**
     * Ambil (atau buat) record semester yang sedang berjalan saat ini.
     * Jika belum ada record yang cocok dengan periode+tahun ajaran berjalan,
     * buat record baru memakai default.
     */
    public static function currentOrCreate(): self
    {
        $def = self::defaultForDate(Carbon::now());

        return self::firstOrCreate(
            ['periode' => $def['periode'], 'tahun_ajaran' => $def['tahun_ajaran']],
            ['tanggal_mulai' => $def['tanggal_mulai'], 'tanggal_selesai' => $def['tanggal_selesai']]
        );
    }
}
