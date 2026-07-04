<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomCpmkAssessmentScore extends Model
{
    protected $table = 'obe_kelas_cpmk_nilai';

    protected $fillable = [
        'classroom_cpmk_assessment_id',
        'student_id',
        'score',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function assessment()
    {
        return $this->belongsTo(ClassroomCpmkAssessment::class, 'classroom_cpmk_assessment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
