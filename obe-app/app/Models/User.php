<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'obe_pengguna';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'identity',
        'initials',
        'role',
        'jabatan_akademik',
        'jurusan_id',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ── Konstanta role struktural yang secara akademik adalah dosen ── */

    /**
     * Role-role yang secara fungsional/struktural "punya jabatan",
     * namun tetap berstatus dosen secara akademik.
     */
    public const ROLES_STRUKTURAL_DOSEN = ['kaprodi', 'kajur', 'dekan', 'wakil_dekan'];

    /**
     * Semua role yang dianggap "dosen" untuk keperluan penugasan kelas,
     * dropdown dosen, dan query akademik.
     * Gunakan metode ini agar konsisten di seluruh aplikasi.
     */
    public static function rolesDosen(): array
    {
        return ['dosen', ...self::ROLES_STRUKTURAL_DOSEN];
    }

    /* ── Helper: apakah user ini berstatus dosen (struktural atau murni) ── */

    /**
     * Apakah user adalah dosen (murni maupun yang sedang menjabat struktural).
     * Cek dari jabatan_akademik ATAU role.
     */
    public function isDosen(): bool
    {
        return $this->jabatan_akademik === 'dosen' || $this->role === 'dosen';
    }

    /**
     * Apakah user sedang menjabat struktural (kaprodi, kajur, dekan, wakil_dekan).
     */
    public function isMenjabat(): bool
    {
        return in_array($this->role, self::ROLES_STRUKTURAL_DOSEN);
    }

    /* ── Relationships ── */

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'obe_mata_kuliah_pengguna', 'user_id', 'course_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'obe_kelas_pengguna', 'user_id', 'classroom_id');
    }

    public function jurusan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAdminJurusan(): bool
    {
        return $this->role === 'admin_jurusan';
    }

    public function profilKaprodi()  { return $this->hasOne(KaprodiProfile::class, 'user_id'); }
    public function profilDosen()    { return $this->hasOne(DosenProfile::class, 'user_id'); }
    public function profilPjLab()    { return $this->hasOne(PjLabProfile::class, 'user_id'); }
    public function profilTendik()   { return $this->hasOne(TendikProfile::class, 'user_id'); }
    public function profilMahasiswa(){ return $this->hasOne(MahasiswaProfile::class, 'user_id'); }

    public function jabatan()
    {
        return $this->hasMany(Pengelola::class, 'user_id');
    }

    public function pengelola()
    {
        return $this->hasOne(Pengelola::class, 'user_id')
                    ->where('aktif', true)
                    ->latest();
    }

    // Helper role
    public function isPimpinan(): bool
    {
        return in_array($this->role, ['dekan', 'wakil_dekan']);
    }

    public function jabatanAktif()
    {
        return $this->jabatan()->where('aktif', true);
    }

    public function notifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function readNotifications()
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    /* ── Query Scopes ── */

    /**
     * Scope: semua user yang berstatus dosen (role dosen murni
     * maupun struktural dengan jabatan_akademik = 'dosen').
     *
     * Penggunaan: User::dosenAkademik()->get()
     */
    public function scopeDosenAkademik($query)
    {
        return $query->where(function ($q) {
            $q->where('role', 'dosen')
              ->orWhere('jabatan_akademik', 'dosen');
        });
    }

    /* ── Kaprodi scope helpers ─────────────────────────────────────── */

    /**
     * Ambil program_studi_id aktif untuk user yang sedang login.
     *
     * Logika:
     *  - Kaprodi  → dari KaprodiProfile::program_studi_id
     *  - Kajur    → null (scope by jurusan, bukan prodi)
     *  - Admin jurusan mode admin_prodi → dari session active_prodi_id
     *  - Lainnya  → null
     *
     * Gunakan ini di controller agar konsisten di seluruh aplikasi.
     */
    public function activeProdiId(): ?int
    {
        if ($this->role === 'kaprodi') {
            return $this->profilKaprodi?->program_studi_id;
        }

        if ($this->role === 'admin_jurusan' && session('role_mode') === 'admin_prodi') {
            return session('active_prodi_id') ? (int) session('active_prodi_id') : null;
        }

        return null;
    }

    /**
     * Apakah user ini adalah kaprodi dengan prodi terkonfigurasi?
     */
    public function isKaprodiDenganProdi(): bool
    {
        return $this->role === 'kaprodi' && !is_null($this->activeProdiId());
    }
}