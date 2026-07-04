<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'indicator_id',
        'name',
        'description',
        'percentage',
        'is_auto',
    ];

    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class);
    }
}
