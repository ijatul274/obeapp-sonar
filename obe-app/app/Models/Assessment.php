<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $table = 'obe_komponen_penilaian';

    protected $fillable = [
        'indicator_id',
        'classroom_id',   // ← per-kelas: setiap dosen di kelas berbeda punya komponen sendiri
        'name',
        'description',
        'percentage',
        'is_auto',
    ];

    protected $casts = [
        'is_auto'    => 'boolean',
        'percentage' => 'decimal:2',
    ];

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class);
    }
}