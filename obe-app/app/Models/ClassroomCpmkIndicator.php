<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomCpmkIndicator extends Model
{
    protected $table = 'obe_kelas_cpmk_indikator';

    protected $fillable = [
        'classroom_cpmk_id',
        'description',
        'percentage',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function cpmk()
    {
        return $this->belongsTo(ClassroomCpmk::class, 'classroom_cpmk_id');
    }

    public function assessments()
    {
        return $this->hasMany(ClassroomCpmkAssessment::class);
    }
}
