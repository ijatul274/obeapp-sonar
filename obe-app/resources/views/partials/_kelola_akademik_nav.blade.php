@php
    $auth     = auth()->user();
    $isSuper  = $auth && $auth->role === 'admin';

    $akademikTabs = [
        ['label' => 'Semester',    'route' => 'admin.semester.index',    'match' => 'admin.semester.*',    'count' => null,                                       'super' => false],
        ['label' => 'Konsentrasi', 'route' => 'admin.konsentrasi.index', 'match' => 'admin.konsentrasi.*', 'count' => \App\Models\Konsentrasi::count(),           'super' => false],
        ['label' => 'Jurusan',     'route' => 'admin.jurusan.index',     'match' => 'admin.jurusan.*',     'count' => \App\Models\Jurusan::count(),               'super' => true],
        ['label' => 'Program Studi','route' => 'admin.prodi.index',      'match' => 'admin.prodi.*',       'count' => \App\Models\ProgramStudi::count(),          'super' => true],
    ];
    // Hanya superadmin yang lihat tab Jurusan & Prodi
    $akademikTabs = collect($akademikTabs)->reject(fn($t) => $t['super'] && !$isSuper)->values();
@endphp

<nav class="obe-subnav" aria-label="Sub-menu Kelola Akademik">
    @foreach($akademikTabs as $i => $tab)
        @if($i > 0)<span class="obe-subnav__divider">|</span>@endif
        <a href="{{ route($tab['route']) }}"
           class="obe-subnav__item {{ request()->routeIs($tab['match']) ? 'active' : '' }}">
            {{ $tab['label'] }}
            @if(!is_null($tab['count']))
                <span class="obe-subnav__item-count">({{ $tab['count'] }})</span>
            @endif
        </a>
    @endforeach
</nav>
