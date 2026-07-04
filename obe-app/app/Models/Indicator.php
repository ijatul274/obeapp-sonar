<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    protected $table = 'obe_indikator';

    protected $fillable = [
        'cpmk_id',
        'description',
        'percentage',
    ];

    public function cpmk()
    {
        return $this->belongsTo(Cpmk::class);
    }

    /**
     * Semua komponen penilaian untuk indikator ini (semua kelas).
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Komponen penilaian untuk indikator ini, difilter per kelas.
     * Dipakai di view dosen agar setiap dosen/kelas punya komponen sendiri.
     *
     * Contoh: $indicator->assessmentsForClassroom($classroomId)
     */
    public function assessmentsForClassroom(int $classroomId)
    {
        return $this->hasMany(Assessment::class)
            ->where('classroom_id', $classroomId);
    }
}