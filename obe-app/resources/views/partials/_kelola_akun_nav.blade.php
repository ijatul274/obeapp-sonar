@php
    use App\Models\User;
    $auth        = auth()->user();
    $isSuper     = $auth && $auth->role === 'admin';
    $isAdminJur  = $auth && $auth->role === 'admin_jurusan';
    $jurusanId   = $isAdminJur ? $auth->jurusan_id : null;
    $isAdminProdi = $isAdminJur && session('role_mode') === 'admin_prodi';
    $activeProdiId = $isAdminProdi ? session('active_prodi_id') : null;

    $countQuery = function (string $r) use ($isAdminJur, $jurusanId, $isAdminProdi, $activeProdiId) {
        if ($r === 'dekan') {
            $q = User::whereIn('role', ['dekan', 'wakil_dekan']);
        } elseif ($r === 'dosen') {
            $q = User::dosenAkademik();
        } else {
            $q = User::where('role', $r);
        }

        if ($isAdminProdi && $activeProdiId) {
            // Mode admin prodi: mahasiswa difilter per prodi, lainnya per jurusan
            if ($r === 'mahasiswa') {
                $q->whereHas('profilMahasiswa', fn($mq) => $mq->where('program_studi_id', $activeProdiId));
            } elseif ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            }
        } elseif ($isAdminJur && $jurusanId) {
            $q->where('jurusan_id', $jurusanId);
        }

        return $q->count();
    };

    // Saat mode admin prodi, hanya tampilkan tab Mahasiswa
    if ($isAdminProdi) {
        $allTabs = [
            ['label' => 'Mahasiswa', 'role' => 'mahasiswa', 'super' => false],
        ];
    } else {
        $allTabs = [
            ['label' => 'Administrator',  'role' => 'admin',          'super' => true],
            ['label' => 'Admin Jurusan',  'role' => 'admin_jurusan',  'super' => true],
            ['label' => 'Pimpinan',       'role' => 'dekan',          'super' => true],
            ['label' => 'Kajur',          'role' => 'kajur',          'super' => false],
            ['label' => 'Kaprodi',        'role' => 'kaprodi',        'super' => false],
            ['label' => 'Dosen',          'role' => 'dosen',          'super' => false],
            ['label' => 'Tendik',         'role' => 'tendik',         'super' => false],
            ['label' => 'PLP',            'role' => 'plp',            'super' => false],
            ['label' => 'Mahasiswa',      'role' => 'mahasiswa',      'super' => false],
        ];
    }

    // Admin jurusan tidak boleh lihat tab admin/admin_jurusan/pimpinan
    $akunTabs = collect($allTabs)
        ->reject(fn($t) => $isAdminJur && $t['super'])
        ->map(fn($t) => array_merge($t, ['count' => $countQuery($t['role'])]))
        ->values();

    $current = request('role');
@endphp

<nav class="obe-subnav" aria-label="Sub-menu Kelola Akun">
    @foreach($akunTabs as $i => $tab)
        @if($i > 0)<span class="obe-subnav__divider">|</span>@endif
        <a href="{{ route('users.index', ['role' => $tab['role']]) }}"
           class="obe-subnav__item {{ $current === $tab['role'] ? 'active' : '' }}">
            {{ $tab['label'] }}
            <span class="obe-subnav__item-count">({{ $tab['count'] }})</span>
        </a>
    @endforeach
</nav>