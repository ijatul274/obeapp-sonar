<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'obe_mata_kuliah';

    protected $fillable = [
        'jurusan_id',
        'program_studi_id',   // ← BARU: prodi pemilik mata kuliah
        'code',
        'name',
        'sks',
        'semester',
        'wajib_pilihan',
        'prerequisite_course_id',
    ];

    public function jurusan()
    {
        return $this->belongsTo(\App\Models\Jurusan::class, 'jurusan_id');
    }

    /**
     * Program studi pemilik mata kuliah.
     * Dipakai untuk scope kaprodi agar data terisolasi per prodi.
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    /* ── Relationships ─────────────────────────────────── */

    public function users()
    {
        return $this->belongsToMany(User::class, 'obe_mata_kuliah_pengguna', 'course_id', 'user_id');
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'obe_mata_kuliah_cpl');
    }

    public function cpmks()
    {
        return $this->hasMany(Cpmk::class);
    }

    public function prerequisite()
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    /* ── Scopes ────────────────────────────────────────── */

    /** Mata kuliah semester ganjil (1, 3, 5, 7) */
    public function scopeGanjil($query)
    {
        return $query->whereIn('semester', [1, 3, 5, 7]);
    }

    /** Mata kuliah semester genap (2, 4, 6, 8) */
    public function scopeGenap($query)
    {
        return $query->whereIn('semester', [2, 4, 6, 8]);
    }

    /**
     * Scope: filter MK berdasarkan program_studi_id.
     * Dipakai oleh controller agar kaprodi hanya melihat MK prodinya.
     *
     * Contoh: Course::untukProdi($prodiId)->get()
     */
    public function scopeUntukProdi($query, int $prodiId)
    {
        return $query->where('program_studi_id', $prodiId);
    }

    /* ── Helpers ───────────────────────────────────────── */

    public function isGanjil(): bool
    {
        return $this->semester % 2 !== 0;
    }

    public function isGenap(): bool
    {
        return $this->semester % 2 === 0;
    }

    public function getPeriodTypeAttribute(): string
    {
        return $this->isGanjil() ? 'ganjil' : 'genap';
    }
}