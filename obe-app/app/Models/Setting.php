<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'obe_pengaturan';

    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
    ];
}
