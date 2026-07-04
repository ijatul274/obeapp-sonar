<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sks',
        'semester',
        'prerequisite_course_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function cpls()
    {
        return $this->belongsToMany(Cpl::class, 'course_cpl');
    }

    public function cpmks()
    {
        return $this->hasMany(Cpmk::class);
    }

    public function prerequisite()
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }
}
