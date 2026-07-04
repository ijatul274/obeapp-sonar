@props([
    'title' => null,
    'header' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'OBE' }} — {{ config('app.name', 'OBE') }}</title>

    @include('partials.pwa-head')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="{{ asset('css/obe-theme.css') }}?v={{ @filemtime(public_path('css/obe-theme.css')) ?: '4' }}" rel="stylesheet">

    <style>
        table.dataTable.obe-dt thead th {
            background: var(--obe-ink, #111); color: #fff; font-weight: 600;
            border-bottom: 0; white-space: nowrap;
        }
        table.dataTable.obe-dt thead th.dt-orderable-asc,
        table.dataTable.obe-dt thead th.dt-orderable-desc,
        table.dataTable.obe-dt thead th.sorting,
        table.dataTable.obe-dt thead th.sorting_asc,
        table.dataTable.obe-dt thead th.sorting_desc { color: #fff; }
        table.dataTable.obe-dt thead th.sorting:before,
        table.dataTable.obe-dt thead th.sorting:after,
        table.dataTable.obe-dt thead th.sorting_asc:before,
        table.dataTable.obe-dt thead th.sorting_asc:after,
        table.dataTable.obe-dt thead th.sorting_desc:before,
        table.dataTable.obe-dt thead th.sorting_desc:after { color: #fff !important; opacity: .55; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { padding: .5rem .25rem; }
        .obe-dt-toolbar { display:flex; flex-wrap:wrap; gap:.75rem; align-items:center; padding:.25rem .25rem .75rem; }
        .obe-dt-toolbar label { font-size:.85rem; margin-bottom:0; display:inline-flex; align-items:center; gap:.4rem; }
        .obe-dt-toolbar select.form-select { width:auto; display:inline-block; }

        /* ── Role Switch: tombol Bootstrap biasa ── */
        .role-switch-wrap {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--obe-bg, #f5f5f5);
            border: 1px solid var(--obe-line, #dee2e6);
            border-radius: var(--bs-border-radius-pill);
            padding: 3px;
        }
        .role-switch-wrap .btn {
            border-radius: var(--bs-border-radius-pill) !important;
            font-size: .75rem;
            font-weight: 600;
            padding: 3px 14px;
            line-height: 1.4;
            border: none;
        }
        .role-switch-wrap .btn-active {
            background: var(--obe-red, #c0392b);
            color: #fff;
        }
        .role-switch-wrap .btn-inactive {
            background: transparent;
            color: var(--obe-ink-soft, #555);
        }
        .role-switch-wrap .btn-inactive:hover {
            background: var(--obe-line, #dee2e6);
            color: var(--obe-ink, #111);
        }
    </style>
</head>
<body>

@php
    $authUser = auth()->user();
    $role     = $authUser?->role ?? 'mahasiswa';
    $initials = strtoupper(mb_substr($authUser?->name ?? 'U', 0, 1));

    // Role yang bisa switch ke mode dosen
    $bisaSwitch = ['kaprodi', 'kajur', 'dekan', 'wakil_dekan'];
    $isBisaSwitch = in_array($role, $bisaSwitch);

    // Ambil mode dari session; default = role asli
    $roleMode = $isBisaSwitch ? session('role_mode', $role) : $role;

    // Label jabatan untuk tampilan
    $roleLabel = match($role) {
        'admin'         => 'Administrator',
        'admin_jurusan' => 'Admin Jurusan',
        'kaprodi'       => 'Ketua Program Studi',
        'dosen'         => 'Dosen',
        'mahasiswa'     => 'Mahasiswa',
        'dekan'         => 'Dekan',
        'wakil_dekan'   => 'Wakil Dekan',
        'kajur'         => 'Kepala Jurusan',
        default         => ucfirst($role),
    };

    // Label mode aktif
    $roleModeLabel = match($role) {
        'kaprodi'    => 'Kaprodi',
        'kajur'      => 'Kajur',
        'dekan'      => 'Dekan',
        'wakil_dekan'=> 'Wakil Dekan',
        default      => ucfirst($role),
    };

    // effectiveRole = role yang menentukan tampilan sidebar/topnav
    $effectiveRole = ($isBisaSwitch && $roleMode === 'dosen') ? 'dosen' : $role;

    $useTopNav = in_array($effectiveRole, ['admin', 'admin_jurusan', 'kaprodi', 'dosen', 'dekan', 'kajur', 'mahasiswa']);
@endphp

<div class="obe-shell" x-data="{ sidebarOpen: false }">

    <div class="obe-backdrop" :class="{ 'is-open': sidebarOpen }" @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside class="obe-sidebar" :class="{ 'is-open': sidebarOpen }">
        <div class="obe-sidebar__brand">
<img src="{{ asset('images/logo.png') }}" alt="Logo" class="obe-sidebar__brand-logo">
            <div class="obe-sidebar__brand-text">
                <span class="obe-sidebar__brand-title">SATU FT UNRI</span>
            </div>
        </div>
        <div class="obe-sidebar__scroll">
            @switch($effectiveRole)
                @case('admin')
                @case('admin_jurusan')
                    @include('partials.sidebar._admin')
                    @break
                @case('kaprodi')
                    @include('partials.sidebar._kaprodi')
                    @break
                @case('dosen')
                @case('dekan')
                @case('kajur')
                @case('wakil_dekan')
                    @include('partials.sidebar._dosen')
                    @break
                @default
                    @include('partials.sidebar._mahasiswa')
            @endswitch
        </div>
    </aside>

    {{-- Main column --}}
    <div class="obe-main">
        <header class="obe-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="obe-sidebar-toggle" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="obe-topbar__title">{{ $header ?? ($title ?? 'Dashboard') }}</h1>
            </div>

            <div class="obe-topbar__right">

                {{-- ── Role Switch: tampil untuk kaprodi / kajur / dekan / wakil_dekan ── --}}
                @if($isBisaSwitch)
                <div class="role-switch-wrap" title="Ganti tampilan mode">
                    {{-- Tombol mode jabatan (kaprodi/kajur/dekan/dll) --}}
                    <form method="POST" action="{{ route('role.switch') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="mode" value="{{ $role }}">
                        <button type="submit"
                                class="btn {{ $roleMode !== 'dosen' ? 'btn-active' : 'btn-inactive' }}">
                            {{ $roleModeLabel }}
                        </button>
                    </form>
                    {{-- Tombol mode dosen --}}
                    <form method="POST" action="{{ route('role.switch') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="mode" value="dosen">
                        <button type="submit"
                                class="btn {{ $roleMode === 'dosen' ? 'btn-active' : 'btn-inactive' }}">
                            Dosen
                        </button>
                    </form>
                </div>
                @endif

                {{-- Notifikasi --}}
                @if(in_array($role, ['kaprodi', 'dosen']) || $isBisaSwitch)
                    @php
                        $unreadNotifs = $authUser->unreadNotifications->take(5);
                        $unreadCount  = $authUser->unreadNotifications->count();
                    @endphp
                    <div class="dropdown">
                        <button class="btn btn-light position-relative" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                                      style="background:var(--obe-red); font-size:.6rem;">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                            style="width:320px; max-height:400px; overflow-y:auto; border-color:var(--obe-line);">
                            <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom"
                                style="background:var(--obe-bg);">
                                <span class="fw-bold text-uppercase"
                                      style="font-size:.7rem; letter-spacing:.05em; color:var(--obe-ink-soft);">
                                    Notifikasi
                                </span>
                                @if($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0"
                                            style="color:var(--obe-red); font-size:.75rem;">
                                        Tandai semua
                                    </button>
                                </form>
                                @endif
                            </li>
                            @forelse($unreadNotifs as $notif)
                                <li class="px-3 py-2 border-bottom">
                                    <small class="d-block">{{ $notif->data['message'] ?? '-' }}</small>
                                    <div class="text-muted" style="font-size:.7rem;">
                                        {{ $notif->created_at->diffForHumans() }}
                                    </div>
                                    @if(!empty($notif->data['url']))
                                        <a href="{{ $notif->data['url'] }}" class="d-block text-end"
                                           style="color:var(--obe-red); font-size:.75rem;">Lihat &rarr;</a>
                                    @endif
                                </li>
                            @empty
                                <li class="px-3 py-3 text-center text-muted" style="font-size:.8rem;">
                                    Tidak ada notifikasi baru
                                </li>
                            @endforelse
                        </ul>
                    </div>
                @endif

                {{-- User dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span style="width:24px;height:24px;border-radius:50%;background:var(--obe-ink);
                                     color:#fff;display:inline-flex;align-items:center;
                                     justify-content:center;font-weight:700;font-size:.7rem;">
                            {{ $initials }}
                        </span>
                        <span class="d-none d-md-inline">{{ $authUser?->name ?? 'Pengguna' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-color:var(--obe-line);">
                        <li>
                            <span class="dropdown-item-text">
                                <small class="text-muted d-block" style="font-size:.7rem;">Masuk sebagai</small>
                                <strong>{{ $authUser?->name ?? 'Pengguna' }}
</strong>
                                <small class="text-muted d-block" style="font-size:.7rem;">
                                    {{ $roleLabel }}
                                    @if($isBisaSwitch && $roleMode === 'dosen')
                                        <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Mode Dosen</span>
                                    @endif
                                    @if($role === 'admin_jurusan' && session('role_mode') === 'admin_prodi')
                                        <span class="badge ms-1" style="font-size:.65rem; background:var(--obe-red);">
                                            Admin Prodi · {{ session('active_prodi_nama') }}
                                        </span>
                                    @endif
                                </small>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Log Out</button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </header>

@if($authUser && $useTopNav && !request()->routeIs('admin.obe.*'))
            <nav class="obe-topnav">
                @switch($effectiveRole)
                    @case('admin')
                    @case('admin_jurusan')
                        @include('partials.topnav._admin')
                        @break
                    @case('kaprodi')
                        @include('partials.topnav._kaprodi')
                        @break
                    @case('dosen')
                    @case('dekan')
                    @case('kajur')
                    @case('wakil_dekan')
                        @include('partials.topnav._dosen')
                        @break
                    @case('mahasiswa')
                        @include('partials.topnav._mahasiswa')
                        @break
                @endswitch
            </nav>
        @endif

        <main class="obe-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="obe-footer">
            Dikembangkan oleh Prodi Teknik Informatika UNRI
            (<a href="{{ route('tim-pengembang') }}" class="obe-footer__credit text-decoration-none">Izzathul Mardiyah</a>)
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') return;
        const $ = window.jQuery;

        // Suppress DataTables alert popup (e.g. saat tabel kosong / column count mismatch)
        $.fn.dataTable.ext.errMode = 'none';

        $('table.obe-dt').each(function () {
            const $tbl = $(this);
            if ($.fn.DataTable.isDataTable(this)) return;

            // Jika tabel kosong (hanya ada 1 baris dengan colspan), skip inisialisasi DataTable
            const $tbody = $tbl.find('tbody');
            const $rows = $tbody.find('tr');
            if ($rows.length === 1 && $rows.first().find('td[colspan]').length > 0) return;

            const noSort    = ($tbl.data('no-sort') || '').toString().split(',').map(s => s.trim()).filter(Boolean).map(Number);
            const filterCols = ($tbl.data('filter-cols') || '').toString().split(',').map(s => s.trim()).filter(Boolean);
            const orderRaw  = $tbl.data('order');
            const pageLen   = parseInt($tbl.data('page-length') || 50, 10);

            const colDefs = [];
            if (noSort.length) colDefs.push({ targets: noSort, orderable: false, searchable: false });

            const dt = $tbl.DataTable({
                pageLength: pageLen,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                order: orderRaw ? JSON.parse(orderRaw) : [],
                columnDefs: colDefs,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 entri',
                    infoFiltered: '(disaring dari _MAX_ total)',
                    zeroRecords: 'Tidak ditemukan data yang sesuai',
                    emptyTable: 'Tidak ditemukan data yang sesuai',
                    paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' }
                },
                dom: "<'obe-dt-toolbar'<'me-auto'l><'obe-dt-filters'>f>rt<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>"
            });

            if (filterCols.length) {
                const $filterHost = $tbl.closest('.dataTables_wrapper').find('.obe-dt-filters');
                filterCols.forEach(spec => {
                    const [idxStr, label] = spec.split(':');
                    const idx = parseInt(idxStr, 10);
                    if (isNaN(idx)) return;
                    const $sel = $('<select class="form-select form-select-sm"><option value="">Semua</option></select>');
                    const seen = new Set();
                    dt.column(idx).data().each(function (val) {
                        const txt = $('<div>').html(val).text().trim();
                        if (txt && !seen.has(txt)) { seen.add(txt); $sel.append($('<option>').val(txt).text(txt)); }
                    });
                    $sel.on('change', function () {
                        const v = $(this).val();
                        dt.column(idx).search(v ? '^' + $.fn.dataTable.util.escapeRegex(v) + '$' : '', true, false).draw();
                    });
                    const $lbl = $('<label class="me-2"></label>').text((label || 'Filter') + ': ').append($sel);
                    $filterHost.append($lbl);
                });
            }
        });
    });
</script>
</body>
</html>