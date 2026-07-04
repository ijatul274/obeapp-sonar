<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'obe_jurusan';

    protected $fillable = ['nama_jurusan', 'id_prodi', 'kode'];

    /**
     * Prodi yang berada di bawah jurusan ini.
     * (via obe_program_studi.jurusan_id)
     */
    public function prodi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramStudi::class, 'jurusan_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'jurusan_id');
    }
}
