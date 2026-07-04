{{-- Sub-navbar Laporan Nilai — style sama dengan obe-subnav di Kelola Akun --}}
<nav class="obe-subnav" aria-label="Sub-menu Laporan Nilai">
    <a href="{{ route('kaprodi.laporan.index') }}"
       class="obe-subnav__item {{ request()->routeIs('kaprodi.laporan.index') || request()->routeIs('kaprodi.laporan.show') ? 'active' : '' }}">
        Kelas
    </a>
    <span class="obe-subnav__divider">|</span>
    <a href="{{ route('kaprodi.laporan.mahasiswa') }}"
       class="obe-subnav__item {{ request()->routeIs('kaprodi.laporan.mahasiswa') ? 'active' : '' }}">
        Mahasiswa
    </a>
</nav>