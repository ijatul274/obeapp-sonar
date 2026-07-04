<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomCpmk extends Model
{
    protected $table = 'obe_kelas_cpmk';

    protected $fillable = [
        'classroom_id',
        'cpl_id',
        'created_by',
        'code',
        'description',
        'percentage',
        'meeting_start',
        'meeting_end',
        'status',          // draft | pending | approved | rejected
        'rejection_note',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────── */

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function cpl()
    {
        return $this->belongsTo(Cpl::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function indicators()
    {
        return $this->hasMany(ClassroomCpmkIndicator::class);
    }

    /* ── Scopes ────────────────────────────────────────── */

    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopePending($q)  { return $q->where('status', 'pending');  }
    public function scopeDraft($q)    { return $q->where('status', 'draft');    }
    public function scopeRejected($q) { return $q->where('status', 'rejected'); }

    /* ── Helpers ───────────────────────────────────────── */

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'pending'  => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => 'Tidak Diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'gray',
            'pending'  => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default    => 'gray',
        };
    }

    public function getMeetingRangeAttribute(): string
    {
        if ($this->meeting_start && $this->meeting_end) {
            return "Pertemuan {$this->meeting_start}–{$this->meeting_end}";
        }
        return '—';
    }
}
