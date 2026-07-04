{{--
    Sidebar mahasiswa: hanya entri OBE.
    Semua menu utama (Daftar Kelas, Hasil Studi) dipindahkan ke topnavbar.
--}}
@php
    $obeActive = request()->routeIs('mahasiswa.*');
@endphp

<a href="{{ route('mahasiswa.dashboard') }}" class="obe-sidebar__link {{ $obeActive ? 'active' : '' }}">
    <svg class="obe-sidebar__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="obe-sidebar__link-text">OBE</span>
</a>
