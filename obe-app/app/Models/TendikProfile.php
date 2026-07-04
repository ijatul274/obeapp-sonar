<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TendikProfile extends Model
{
    protected $table = 'obe_tendik';

    protected $fillable = ['user_id', 'nip', 'singkatan'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
