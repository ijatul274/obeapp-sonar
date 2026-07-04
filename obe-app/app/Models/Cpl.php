<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cpl extends Model
{
    protected $table = 'obe_cpl';

    protected $fillable = ['program_studi_id', 'code', 'description', 'min_target'];

    protected $casts = ['min_target' => 'decimal:2'];

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'obe_mata_kuliah_cpl');
    }

    public function cpmks()
    {
        return $this->hasMany(Cpmk::class);
    }
}