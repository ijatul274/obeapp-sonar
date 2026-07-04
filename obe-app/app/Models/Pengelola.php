<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengelola extends Model
{
    protected $table = 'obe_pengelola';

    protected $fillable = [
        'user_id',
        'jabatan',
        'bidang',
        'keterangan',
        'mulai_menjabat',
        'selesai_menjabat',
        'aktif',
        'tanda_tangan',
    ];

    protected $casts = [
        'mulai_menjabat'   => 'date',
        'selesai_menjabat' => 'date',
        'aktif'            => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', true);
    }
}
