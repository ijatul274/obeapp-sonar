<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'obe_kelas';

    protected $fillable = [
        'name',
        'semester',
        'academic_year',
        'period_type',
        'lecturer_id',
        'course_id',
        'enrollment_code',
        'is_archived',
        'kaprodi_snapshot',
        'archived_at',
        'satu_unri_bobot',
    ];

    protected $casts = [
        'is_archived'     => 'boolean',
        'archived_at'     => 'datetime',
        'satu_unri_bobot' => 'array',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'obe_kelas_pengguna', 'classroom_id', 'user_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function cpmks()
    {
        return $this->belongsToMany(Cpmk::class, 'obe_kelas_cpmk_dosen')
                    ->withPivot('lecturer_id')
                    ->withTimestamps();
    }

    public function classroomCpmks()
    {
        return $this->hasMany(ClassroomCpmk::class);
    }

    public function cpmkLecturers()
    {
        return $this->belongsToMany(User::class, 'obe_kelas_cpmk_dosen', 'classroom_id', 'lecturer_id')
                    ->distinct();
    }

    /* ── Helpers ───────────────────────────────────────── */

    /**
     * Label periode (mis. "Ganjil 2024/2025")
     */
    public function getPeriodLabelAttribute(): string
    {
        $period = $this->period_type ? ucfirst($this->period_type) : '-';
        $year   = $this->academic_year ?? '-';
        return "{$period} {$year}";
    }

    /**
     * Hitung academic_year & period_type berbasis tanggal yang dapat dikonfigurasi
     * via obe_pengaturan: period_ganjil_start, period_ganjil_end,
     * period_genap_start, period_genap_end (format MM-DD).
     */
    public static function currentPeriod(): array
    {
        $today = now();
        $year  = (int) $today->year;
        $mmdd  = $today->format('m-d');

        $get = function (string $key, string $default): string {
            $row = Setting::where('key', $key)->first();
            return $row?->value ?: $default;
        };

        $ganjilStart = $get('period_ganjil_start', '08-01');
        $ganjilEnd   = $get('period_ganjil_end',   '01-31');
        $genapStart  = $get('period_genap_start',  '02-01');
        $genapEnd    = $get('period_genap_end',    '07-31');

        // Genap: rentang dalam tahun yang sama (start <= today <= end).
        if ($mmdd >= $genapStart && $mmdd <= $genapEnd) {
            return [
                'period_type'   => 'genap',
                'academic_year' => ($year - 1) . '/' . $year,
            ];
        }

        // Ganjil: rentang lintas tahun (start..12-31) atau (01-01..end).
        if ($mmdd >= $ganjilStart) {
            return [
                'period_type'   => 'ganjil',
                'academic_year' => $year . '/' . ($year + 1),
            ];
        }

        if ($mmdd <= $ganjilEnd) {
            return [
                'period_type'   => 'ganjil',
                'academic_year' => ($year - 1) . '/' . $year,
            ];
        }

        // Fallback: gap antar rentang — jatuhkan ke ganjil tahun berjalan.
        return [
            'period_type'   => 'ganjil',
            'academic_year' => $year . '/' . ($year + 1),
        ];
    }

    /**
     * Arsipkan otomatis semua kelas yang periodenya sudah lewat (tidak match
     * dengan currentPeriod). Dijalankan sekali per hari (cache).
     */
    public static function autoArchiveExpired(): int
    {
        $cacheKey = 'classrooms.auto_archived_at';
        $today    = now()->toDateString();

        if (\Illuminate\Support\Facades\Cache::get($cacheKey) === $today) {
            return 0;
        }

        $current = self::currentPeriod();

        $count = self::where('is_archived', false)
            ->where(function ($q) use ($current) {
                $q->where('period_type', '!=', $current['period_type'])
                  ->orWhere('academic_year', '!=', $current['academic_year']);
            })
            ->update([
                'is_archived' => true,
                'archived_at' => now(),
            ]);

        \Illuminate\Support\Facades\Cache::put($cacheKey, $today, now()->endOfDay());

        return (int) $count;
    }
}
