@php
    $countAktif   = auth()->user()->classrooms()->where('is_archived', false)->count();
    $countRiwayat = auth()->user()->classrooms()->where('is_archived', true)->count();
@endphp

<a href="{{ route('mahasiswa.dashboard') }}" class="obe-topnav__link {{ request()->routeIs('mahasiswa.dashboard') || request()->routeIs('mahasiswa.classrooms.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10"/></svg>
    <span class="obe-topnav__link-text">Daftar Kelas</span>
    @if($countAktif > 0)
        <span class="obe-topnav__link-badge">{{ $countAktif }}</span>
    @endif
</a>

<a href="{{ route('mahasiswa.riwayat') }}" class="obe-topnav__link {{ request()->routeIs('mahasiswa.riwayat') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="obe-topnav__link-text">Riwayat Kelas</span>
    @if($countRiwayat > 0)
        <span class="obe-topnav__link-badge">{{ $countRiwayat }}</span>
    @endif
</a>

<a href="{{ route('mahasiswa.transkrip') }}" class="obe-topnav__link {{ request()->routeIs('mahasiswa.transkrip') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <span class="obe-topnav__link-text">Hasil Studi</span>
</a>