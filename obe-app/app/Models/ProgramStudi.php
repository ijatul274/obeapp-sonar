<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    protected $table = 'obe_program_studi';

    protected $fillable = ['nama_prodi', 'visi', 'kode', 'jurusan_id'];

    public function jurusan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }
}
