<x-sidebar-layout :title="'Dashboard Admin'" :header="'Dashboard Administrator'">

    @php
        $authUser     = auth()->user();
        $isAdminJur   = $authUser->role === 'admin_jurusan';
        $isAdminProdi = $isAdminJur && session('role_mode') === 'admin_prodi';
    @endphp

    {{-- ── Kartu statistik ── --}}
    <div class="row g-3 mb-4">
        @php
            if ($isAdminProdi) {
                // Mode admin prodi: hanya tampilkan kartu mahasiswa
                $cards = [
                    ['label'=>'Mahasiswa', 'role'=>'mahasiswa', 'count'=>$userStats['mahasiswa']],
                ];
            } elseif ($isAdminJur) {
                $cards = [
                    ['label'=>'Kajur',      'role'=>'kajur',      'count'=>$userStats['kajur']],
                    ['label'=>'Kaprodi',    'role'=>'kaprodi',    'count'=>$userStats['kaprodi']],
                    ['label'=>'Dosen',      'role'=>'dosen',      'count'=>$userStats['dosen']],
                    ['label'=>'PLP',        'role'=>'plp',        'count'=>$userStats['plp']],
                    ['label'=>'Mahasiswa',  'role'=>'mahasiswa',  'count'=>$userStats['mahasiswa']],
                ];
            } else {
                $cards = [
                    ['label'=>'Administrator',  'role'=>'admin',          'count'=>$userStats['admin']],
                    ['label'=>'Admin Jurusan',  'role'=>'admin_jurusan',  'count'=>$userStats['admin_jurusan']],
                    ['label'=>'Kajur',          'role'=>'kajur',          'count'=>$userStats['kajur']],
                    ['label'=>'Kaprodi',        'role'=>'kaprodi',        'count'=>$userStats['kaprodi']],
                    ['label'=>'Dosen',          'role'=>'dosen',          'count'=>$userStats['dosen']],
                    ['label'=>'Tendik',         'role'=>'tendik',         'count'=>$userStats['tendik']],
                    ['label'=>'PLP',            'role'=>'plp',            'count'=>$userStats['plp']],
                    ['label'=>'Mahasiswa',      'role'=>'mahasiswa',      'count'=>$userStats['mahasiswa']],
                ];
            }
        @endphp

        @foreach($cards as $c)
            <div class="col-6 col-lg-3">
                <a href="{{ route('users.index', ['role'=>$c['role']]) }}" class="obe-stat-card d-block text-decoration-none">
                    <div class="obe-stat-card__label">{{ $c['label'] }}</div>
                    <div class="obe-stat-card__value">{{ $c['count'] }}</div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- ── Tombol Switch Admin Prodi (hanya untuk admin jurusan) ── --}}
    @if($isAdminJur && $prodiJurusan->isNotEmpty())
    <div class="obe-card mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h2 class="obe-card__title mb-0">Mode Admin Prodi</h2>
                <p class="text-muted small mb-0 mt-1">
                    Pilih program studi untuk mengelola mahasiswanya secara terpisah.
                </p>
            </div>
            @if($isAdminProdi)
                {{-- Tombol kembali ke mode admin jurusan --}}
                <form method="POST" action="{{ route('role.switch') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="mode" value="admin_jurusan">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        ← Kembali ke Admin Jurusan
                    </button>
                </form>
            @endif
        </div>

        <div class="d-flex flex-wrap gap-2">
            @foreach($prodiJurusan as $prodi)
                @php
                    $isActive = $isAdminProdi && session('active_prodi_id') == $prodi->id;
                @endphp
                <form method="POST" action="{{ route('role.switch') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="mode" value="admin_prodi">
                    <input type="hidden" name="prodi_id" value="{{ $prodi->id }}">
                    <button type="submit"
                            class="btn btn-sm {{ $isActive ? 'btn-dark' : 'btn-outline-dark' }}"
                            title="Kelola mahasiswa {{ $prodi->nama_prodi }}">
                        @if($isActive)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="me-1"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                        {{ $prodi->nama_prodi }}
                        @if($prodi->kode)
                            <span class="opacity-75">({{ $prodi->kode }})</span>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>

        @if($isAdminProdi)
            <div class="mt-3 px-3 py-2 rounded" style="background:var(--obe-red-soft); border-left: 3px solid var(--obe-red);">
                <small style="color:var(--obe-red);">
                    <strong>Mode aktif:</strong> Admin Prodi — {{ session('active_prodi_nama') }}
                    &nbsp;·&nbsp; Hanya data mahasiswa prodi ini yang ditampilkan.
                </small>
            </div>
        @endif
    </div>
    @endif

    {{-- ── Total pengguna ── --}}
    <div class="obe-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="obe-card__title">Total Pengguna</h2>
            <span class="badge" style="background:var(--obe-red-soft); color:var(--obe-red);">{{ $userStats['total'] }} akun</span>
        </div>
        <p class="text-muted small mb-0">Klik kartu di atas untuk mengelola pengguna per peran.</p>
    </div>

</x-sidebar-layout>