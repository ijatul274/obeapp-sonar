<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cpl extends Model
{
    protected $fillable = ['code', 'description'];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_cpl');
    }
}
