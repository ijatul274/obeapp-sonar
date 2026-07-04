<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomCpmkAssessment extends Model
{
    protected $table = 'obe_kelas_cpmk_komponen';

    protected $fillable = [
        'classroom_cpmk_indicator_id',
        'name',
        'description',
        'percentage',
        'is_auto',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function indicator()
    {
        return $this->belongsTo(ClassroomCpmkIndicator::class, 'classroom_cpmk_indicator_id');
    }

    public function scores()
    {
        return $this->hasMany(ClassroomCpmkAssessmentScore::class);
    }
}
