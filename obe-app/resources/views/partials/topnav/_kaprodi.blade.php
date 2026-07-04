@php
    $auth      = auth()->user();
    $prodiId   = $auth?->activeProdiId();
    $jurusanId = $auth?->jurusan_id;

    // Count CPL & Profil Lulusan: scope KETAT ke prodi kaprodi
    $countCpl    = \App\Models\Cpl::when($prodiId, fn($q) => $q->where('program_studi_id', $prodiId))->count();
    $countProfil = \App\Models\GraduateProfile::when($prodiId, fn($q) => $q->where('program_studi_id', $prodiId))->count();

    // Count Kelas: scope ke prodi via course.program_studi_id
    $classroomQ = \App\Models\Classroom::query();
    if ($prodiId && $auth->role === 'kaprodi') {
        $classroomQ->whereHas('course', fn($q) => $q->where('program_studi_id', $prodiId));
    } elseif ($jurusanId && $auth->role === 'kaprodi') {
        $classroomQ->where(function ($q) use ($jurusanId) {
            $q->whereHas('lecturer', fn($lq) => $lq->where('jurusan_id', $jurusanId))
              ->orWhereHas('cpmkLecturers', fn($lq) => $lq->where('jurusan_id', $jurusanId));
        });
    }

    // Count MK langsung dari tabel course (lebih akurat dari distinct classroom)
    $countCourse   = \App\Models\Course::when($prodiId, fn($q) => $q->where('program_studi_id', $prodiId))->count();
    $countActive   = (clone $classroomQ)->where('is_archived', false)->count();
    $countArchived = (clone $classroomQ)->where('is_archived', true)->count();
@endphp

<a href="{{ route('kaprodi.dashboard') }}" class="obe-topnav__link {{ request()->routeIs('kaprodi.dashboard') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10"/></svg>
    <span class="obe-topnav__link-text">Dashboard</span>
</a>

<a href="{{ route('graduate-profiles.index') }}" class="obe-topnav__link {{ request()->routeIs('graduate-profiles.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    <span class="obe-topnav__link-text">Profil Lulusan</span>
    <span class="obe-topnav__link-badge">{{ $countProfil }}</span>
</a>

<a href="{{ route('cpls.index') }}" class="obe-topnav__link {{ request()->routeIs('cpls.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="obe-topnav__link-text">CPL</span>
    <span class="obe-topnav__link-badge">{{ $countCpl }}</span>
</a>

<a href="{{ route('courses.index') }}" class="obe-topnav__link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    <span class="obe-topnav__link-text">Mata Kuliah</span>
    <span class="obe-topnav__link-badge">{{ $countCourse }}</span>
</a>

<a href="{{ route('classrooms.index') }}" class="obe-topnav__link {{ request()->routeIs('classrooms.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z M12 14v7m-7-7l7 4 7-4"/></svg>
    <span class="obe-topnav__link-text">Kelola Kelas</span>
    <span class="obe-topnav__link-badge">{{ $countActive }}</span>
</a>

<a href="{{ route('kaprodi.arsip.index') }}" class="obe-topnav__link {{ request()->routeIs('kaprodi.arsip.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
    <span class="obe-topnav__link-text">Arsip Kelas</span>
    <span class="obe-topnav__link-badge">{{ $countArchived }}</span>
</a>

<a href="{{ route('kaprodi.laporan.index') }}" class="obe-topnav__link {{ request()->routeIs('kaprodi.laporan.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m3 6V7m3 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
    <span class="obe-topnav__link-text">Laporan Nilai</span>
</a>