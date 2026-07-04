<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsentrasi extends Model
{
    protected $table = 'obe_konsentrasi';

    protected $fillable = ['kode', 'nama'];
}
