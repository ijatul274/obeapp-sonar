<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KaprodiProfile extends Model
{
    protected $table = 'obe_kaprodi';

    protected $fillable = ['user_id', 'nip', 'singkatan', 'program_studi_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }
}
