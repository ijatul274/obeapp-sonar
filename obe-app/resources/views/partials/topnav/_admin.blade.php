@php
    use App\Models\User;
    $auth           = auth()->user();
    $isSuper        = $auth && $auth->role === 'admin';
    $isAdminJur     = $auth && $auth->role === 'admin_jurusan';
    $jurusanId      = $isAdminJur ? $auth->jurusan_id : null;
    $isAdminProdi   = $isAdminJur && session('role_mode') === 'admin_prodi';
    $activeProdiId  = $isAdminProdi ? (int) session('active_prodi_id') : null;

    if ($isAdminProdi && $activeProdiId) {
        // Mode admin prodi: badge hanya hitung mahasiswa prodi aktif
        $totalAkun = User::where('role', 'mahasiswa')
            ->whereHas('profilMahasiswa', fn($q) => $q->where('program_studi_id', $activeProdiId))
            ->count();
    } else {
        $userQ = User::query();
        if ($isAdminJur && $jurusanId) {
            $userQ->where('jurusan_id', $jurusanId);
        }
        $totalAkun = (clone $userQ)->count();
    }

    try { $countSlides = \App\Models\LoginSlide::count(); }
    catch (\Throwable $e) { $countSlides = 0; }
@endphp

<a href="{{ route('admin.dashboard') }}" class="obe-topnav__link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10"/></svg>
    <span class="obe-topnav__link-text">Dashboard</span>
</a>

<a href="{{ route('admin.kelola-akun.index') }}" class="obe-topnav__link {{ request()->routeIs('users.*') || request()->routeIs('admin.kelola-akun.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <span class="obe-topnav__link-text">Kelola Akun</span>
    <span class="obe-topnav__link-badge">{{ $totalAkun }}</span>
</a>

<a href="{{ route('admin.akademik.index') }}" class="obe-topnav__link {{ request()->routeIs('admin.akademik.*') || request()->routeIs('admin.semester.*') || request()->routeIs('admin.konsentrasi.*') || request()->routeIs('admin.jurusan.*') || request()->routeIs('admin.prodi.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
    <span class="obe-topnav__link-text">Kelola Akademik</span>
</a>

@if($isSuper)
<a href="{{ route('admin.login-slides.index') }}" class="obe-topnav__link {{ request()->routeIs('admin.login-slides.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
    <span class="obe-topnav__link-text">Carousel Login</span>
    <span class="obe-topnav__link-badge">{{ $countSlides }}</span>
</a>

<a href="{{ route('settings.index') }}" class="obe-topnav__link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <span class="obe-topnav__link-text">Pengaturan</span>
</a>
@endif