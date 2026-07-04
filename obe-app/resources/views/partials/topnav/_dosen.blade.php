@php
    use App\Models\Classroom;
    $user = auth()->user();
    $countKelas = Classroom::where('is_archived', false)
        ->where(function ($q) use ($user) {
            $q->where('lecturer_id', $user->id)
              ->orWhereExists(function ($q2) use ($user) {
                  $q2->select(\DB::raw(1))
                     ->from('obe_kelas_cpmk_dosen')
                     ->whereColumn('obe_kelas_cpmk_dosen.classroom_id', 'obe_kelas.id')
                     ->where('obe_kelas_cpmk_dosen.lecturer_id', $user->id);
              });
        })->count();
    $countRiwayat = Classroom::where('is_archived', true)
        ->where(function ($q) use ($user) {
            $q->where('lecturer_id', $user->id)
              ->orWhereExists(function ($q2) use ($user) {
                  $q2->select(\DB::raw(1))
                     ->from('obe_kelas_cpmk_dosen')
                     ->whereColumn('obe_kelas_cpmk_dosen.classroom_id', 'obe_kelas.id')
                     ->where('obe_kelas_cpmk_dosen.lecturer_id', $user->id);
              });
        })->count();
@endphp

<a href="{{ route('dosen.pemetaan') }}" class="obe-topnav__link {{ request()->routeIs('dosen.pemetaan') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10"/></svg>
    <span class="obe-topnav__link-text">Dashboard</span>
</a>

<a href="{{ route('dosen.dashboard') }}" class="obe-topnav__link {{ request()->routeIs('dosen.dashboard') || request()->routeIs('dosen.classrooms.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z M12 14v7m-7-7l7 4 7-4"/></svg>
    <span class="obe-topnav__link-text">Daftar Kelas</span>
    <span class="obe-topnav__link-badge">{{ $countKelas }}</span>
</a>

<a href="{{ route('dosen.riwayat.index') }}" class="obe-topnav__link {{ request()->routeIs('dosen.riwayat.*') ? 'active' : '' }}">
    <svg class="obe-topnav__link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
    <span class="obe-topnav__link-text">Riwayat Kelas</span>
    <span class="obe-topnav__link-badge">{{ $countRiwayat }}</span>
</a>