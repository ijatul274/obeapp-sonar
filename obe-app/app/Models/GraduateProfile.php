<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GraduateProfile extends Model
{
    protected $table = 'obe_profil_lulusan';

    protected $fillable = ['program_studi_id', 'name', 'description'];

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }
}