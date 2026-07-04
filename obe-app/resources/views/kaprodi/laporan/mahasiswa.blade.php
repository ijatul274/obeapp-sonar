<x-sidebar-layout :title="'Laporan Mahasiswa'" :header="'Laporan Nilai'">

    @include('kaprodi.laporan._subnav')

    {{-- Filter --}}
    <div class="obe-card mb-3">
        <form method="GET" action="{{ route('kaprodi.laporan.mahasiswa') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-uppercase" style="font-size:.7rem;">Tahun Angkatan</label>
                <select name="angkatan" onchange="this.form.submit()" class="form-select form-select-sm">
                    <option value="">Semua Angkatan</option>
                    @foreach($angkatanList as $ang)
                        <option value="{{ $ang }}" {{ $filterAngkatan === $ang ? 'selected' : '' }}>{{ $ang }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <a href="{{ route('kaprodi.laporan.mahasiswa') }}" class="btn btn-obe-outline btn-sm w-100">Reset Filter</a>
            </div>
        </form>
    </div>

    <div class="obe-card p-0 overflow-hidden">
        <div class="px-3 py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2"
             style="background:var(--obe-bg);">
            <div>
                <h2 class="obe-card__title mb-0">Ketercapaian CPL per Mahasiswa</h2>
                <small class="text-muted">
                    {{ $allStudents->count() }} mahasiswa &bull; {{ $cpls->count() }} CPL
                    @if($filterAngkatan) &bull; Angkatan {{ $filterAngkatan }} @endif
                </small>
            </div>
            <div class="d-lg-none small text-muted fst-italic">Geser ke kanan untuk lihat semua CPL.</div>
        </div>

        @if($allStudents->isEmpty())
            <div class="text-center py-5 text-muted">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     class="mb-3 d-block mx-auto" style="opacity:.3">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="mb-0 fw-semibold">Belum ada mahasiswa terdaftar</p>
                <small>pada filter yang dipilih.</small>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size:.8rem; min-width: {{ 300 + $cpls->count() * 80 }}px;">
                    <thead>
                        <tr style="background:var(--obe-ink);">
                            <th class="text-center" style="width:40px; position:sticky; left:0; background:var(--obe-ink); z-index:2; color:#fff;">No</th>
                            <th style="min-width:120px; position:sticky; left:40px; background:var(--obe-ink); z-index:2; color:#fff;">NIM</th>
                            <th style="min-width:180px; position:sticky; left:160px; background:var(--obe-ink); z-index:2; color:#fff;">Nama Mahasiswa</th>
                            <th class="text-center" style="width:80px; background:var(--obe-ink); color:#fff;">Angkatan</th>
                            @foreach($cpls as $cpl)
                                <th class="text-center" style="min-width:75px; background:var(--obe-ink); color:#fff;"
                                    title="{{ $cpl->description }}">
                                    {{ $cpl->code }}
                                    @if($cpl->min_target)
                                        <div class="fw-normal" style="font-size:.6rem; color:#9ca3af;">min {{ $cpl->min_target }}%</div>
                                    @endif
                                </th>
                            @endforeach
                            <th class="text-center" style="min-width:80px; position:sticky; right:0; background:var(--obe-ink); color:#fff;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allStudents as $i => $student)
                            @php
                                $nim      = $student->profilMahasiswa?->nim ?? $student->identity ?? '-';
                                $prefix2  = substr($nim, 0, 2);
                                $angkatan = is_numeric($prefix2) ? '20' . $prefix2 : '-';
                            @endphp
                            <tr>
                                <td class="text-center text-muted small"
                                    style="position:sticky; left:0; background:#fff; z-index:1;">{{ $i + 1 }}</td>
                                <td style="font-family:monospace; font-size:.78rem;
                                           position:sticky; left:40px; background:#fff; z-index:1;">
                                    {{ $nim }}
                                </td>
                                <td style="position:sticky; left:160px; background:#fff; z-index:1;">
                                    <a href="{{ route('kaprodi.laporan.mahasiswa.show', $student) }}"
                                       class="fw-semibold text-decoration-none"
                                       style="color:var(--obe-ink);"
                                       onmouseover="this.style.color='var(--obe-red)'"
                                       onmouseout="this.style.color='var(--obe-ink)'">
                                        {{ $student->name }}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="opacity:.5; margin-left:2px; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $angkatan }}</span>
                                </td>
                                @foreach($cpls as $cpl)
                                    @php
                                        $avg       = $studentCplMap[$student->id][$cpl->id] ?? null;
                                        $minTarget = (float) ($cpl->min_target ?? 70);
                                        $lulus     = $avg !== null && $avg >= $minTarget;
                                        $bgColor   = $avg === null
                                            ? ''
                                            : ($lulus ? 'background:#dcfce7;' : 'background:#fee2e2;');
                                        $txtColor  = $avg === null
                                            ? 'color:var(--obe-ink-soft);'
                                            : ($lulus ? 'color:#166534;' : 'color:#b91c1c;');
                                    @endphp
                                    <td class="text-center fw-bold" style="{{ $bgColor }}{{ $txtColor }}">
                                        @if($avg !== null)
                                            {{ number_format($avg, 1) }}%
                                        @else
                                            <span class="text-muted fw-normal">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center" style="position:sticky; right:0; background:#fff; z-index:1;">
                                    <a href="{{ route('kaprodi.laporan.mahasiswa.show', $student) }}"
                                       class="btn btn-sm btn-obe-red">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legenda --}}
            <div class="px-3 py-2 border-top d-flex flex-wrap gap-3 align-items-center" style="background:var(--obe-bg); font-size:.75rem;">
                <span class="text-muted fw-semibold text-uppercase" style="font-size:.65rem; letter-spacing:.05em;">Keterangan:</span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="width:14px; height:14px; background:#dcfce7; border:1px solid #bbf7d0; border-radius:2px; display:inline-block;"></span>
                    <span class="text-muted">Tercapai (&ge; target minimum)</span>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="width:14px; height:14px; background:#fee2e2; border:1px solid #fecaca; border-radius:2px; display:inline-block;"></span>
                    <span class="text-muted">Belum tercapai (&lt; target minimum)</span>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="width:14px; height:14px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:2px; display:inline-block;"></span>
                    <span class="text-muted">— = belum ada nilai</span>
                </span>
            </div>
        @endif
    </div>

</x-sidebar-layout>