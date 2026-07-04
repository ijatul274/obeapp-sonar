<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cpmk extends Model
{
    protected $table = 'obe_cpmk';

    protected $fillable = [
        'course_id',
        'cpl_id',
        'code',
        'description',
        'lecturer_id',
        'percentage',
        'meeting_start',
        'meeting_end',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function cpl()
    {
        return $this->belongsTo(Cpl::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function indicators()
    {
        return $this->hasMany(Indicator::class);
    }

    /** Return readable meeting range, e.g. "Pertemuan 1–8" */
    public function getMeetingRangeAttribute(): string
    {
        if ($this->meeting_start && $this->meeting_end) {
            return "Pertemuan {$this->meeting_start}–{$this->meeting_end}";
        }
        return '—';
    }
}

