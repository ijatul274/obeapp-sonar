<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PjLabProfile extends Model
{
    protected $table = 'obe_pj_lab';

    protected $fillable = ['user_id', 'nip', 'singkatan', 'nama_lab'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
