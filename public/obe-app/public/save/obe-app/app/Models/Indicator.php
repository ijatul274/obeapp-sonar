<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    protected $fillable = [
        'cpmk_id',
        'description',
    ];

    public function cpmk()
    {
        return $this->belongsTo(Cpmk::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
