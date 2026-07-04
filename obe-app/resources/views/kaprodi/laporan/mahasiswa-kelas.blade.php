<x-sidebar-layout :title="'Detail Nilai – ' . $student->name" :header="'Laporan Nilai'">

    @include('kaprodi.laporan._subnav')

    {{-- Breadcrumb / Kembali --}}
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <a href="{{ route('kaprodi.laporan.mahasiswa.show', $student) }}"
               class="btn btn-obe-outline btn-sm mb-2">&larr; Kembali ke Detail Mahasiswa</a>
            <small class="text-muted text-uppercase fw-semibold d-block" style="letter-spacing:.05em; font-size:.7rem;">Detail Nilai</small>
            <p class="text-muted small mb-0 mt-1">
                {{ $course?->code }} — {{ $course?->name }} · {{ ucfirst($classroom->period_type ?? '') }} {{ $classroom->academic_year ?? '' }}
            </p>
            <p class="text-muted small mb-0">
                Mahasiswa: <span class="fw-semibold">{{ $student->name }}</span>
                @php $nim = $student->profilMahasiswa?->nim ?? $student->identity ?? '-'; @endphp
                <span class="badge bg-light text-dark border ms-1" style="font-family:monospace; font-size:.72rem;">{{ $nim }}</span>
            </p>
        </div>
    </div>

    {{-- Info Mata Kuliah --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Kode MK</div>
                <div class="obe-stat-card__value" style="font-family:monospace; font-size:1.1rem;">{{ $course?->code ?? '—' }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Mata Kuliah</div>
                <div class="obe-stat-card__value" style="font-size:1.1rem;">{{ $course?->name ?? '—' }}</div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">SKS</div>
                <div class="obe-stat-card__value">{{ $course?->sks ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Rincian Penilaian --}}
    <div class="obe-card p-0 overflow-hidden">
        <div class="px-4 py-3 border-bottom">
            <h2 class="mb-0 fw-bold" style="font-size:1.5rem;">Rincian Penilaian</h2>
        </div>

        @if(count($cpmkResults) === 0)
            <div class="text-center py-5 text-muted">
                <em>Belum ada data CPMK untuk kelas ini.</em>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle">
                    <thead style="background:#1a1a1a; color:#fff;">
                        <tr>
                            <th style="width:15%;">CPMK</th>
                            <th style="width:25%;">INDIKATOR</th>
                            <th style="width:32%;">KOMPONEN</th>
                            <th style="width:10%; text-align:right;">NILAI</th>
                            <th style="width:9%; text-align:right;">TOTAL IND.</th>
                            <th style="width:9%; text-align:right;">TOTAL CPMK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cpmkResults as $cR)
                            @php
                                $cpmkRowCount = 0;
                                foreach ($cR['indicators'] as $iR) {
                                    $cpmkRowCount += max(count($iR['components']), 1);
                                }
                                if ($cpmkRowCount === 0) $cpmkRowCount = 1;
                                $firstRowOfCpmk = true;
                                $cpmkLulus = $cR['lulus'] ?? ($cR['total'] >= 70);
                            @endphp

                            @if(count($cR['indicators']) === 0)
                                <tr>
                                    <td style="vertical-align:top;">
                                        <span class="badge" style="background:#6366f1; color:#fff; font-family:monospace; font-size:.85rem; padding:.4rem .65rem;">{{ $cR['code'] }}</span>
                                        <div class="small text-muted mt-2">Bobot {{ $cR['weight'] }}%</div>
                                    </td>
                                    <td colspan="2" class="text-muted small fst-italic">Belum ada indikator</td>
                                    <td class="text-end fw-bold">—</td>
                                    <td class="text-end text-muted">—</td>
                                    <td class="text-end text-muted">—</td>
                                </tr>
                            @else
                                @foreach($cR['indicators'] as $j => $iR)
                                    @php
                                        $indRowCount   = max(count($iR['components']), 1);
                                        $firstRowOfInd = true;
                                        $indHasScore   = collect($iR['components'])->contains(fn($c) => $c['raw'] !== null);
                                    @endphp

                                    @if(count($iR['components']) === 0)
                                        <tr>
                                            @if($firstRowOfCpmk)
                                                <td rowspan="{{ $cpmkRowCount }}" style="vertical-align:middle;">
                                                    <span class="badge" style="background:#6366f1; color:#fff; font-family:monospace; font-size:.85rem; padding:.4rem .65rem;">{{ $cR['code'] }}</span>
                                                    <div class="small text-muted mt-2">Bobot {{ $cR['weight'] }}%</div>
                                                </td>
                                                @php $firstRowOfCpmk = false; @endphp
                                            @endif
                                            <td style="vertical-align:top;">
                                                <div class="fw-semibold">Indikator {{ $j+1 }}</div>
                                                <div class="small text-muted mt-1">Bobot {{ $iR['weight'] }}%</div>
                                            </td>
                                            <td class="text-muted small fst-italic">Belum ada komponen penilaian</td>
                                            <td class="text-end fw-bold">—</td>
                                            <td class="text-end text-muted">—</td>
                                            @if($j === 0)
                                                <td rowspan="{{ $cpmkRowCount }}" class="text-end fw-bold align-middle" style="background:#f8f9fa;">—</td>
                                            @endif
                                        </tr>
                                    @else
                                        @foreach($iR['components'] as $k => $comp)
                                            <tr>
                                                @if($firstRowOfCpmk)
                                                    <td rowspan="{{ $cpmkRowCount }}" style="vertical-align:middle;">
                                                        <span class="badge" style="background:#6366f1; color:#fff; font-family:monospace; font-size:.85rem; padding:.4rem .65rem;">{{ $cR['code'] }}</span>
                                                        <div class="small text-muted mt-2">Bobot {{ $cR['weight'] }}%</div>
                                                    </td>
                                                    @php $firstRowOfCpmk = false; @endphp
                                                @endif

                                                @if($firstRowOfInd)
                                                    <td rowspan="{{ $indRowCount }}" style="vertical-align:middle;">
                                                        <div class="fw-semibold">Indikator {{ $j+1 }}</div>
                                                        <div class="small text-muted mt-1">Bobot {{ $iR['weight'] }}%</div>
                                                    </td>
                                                    @php $firstRowOfInd = false; @endphp
                                                @endif

                                                <td>
                                                    {{ $comp['name'] }}
                                                    <span class="text-muted small">({{ rtrim(rtrim(number_format($comp['weight'], 2, '.', ''), '0'), '.') }}%)</span>
                                                </td>

                                                {{-- Nilai komponen --}}
                                                <td class="text-end fw-bold">
                                                    {{ $comp['raw'] !== null ? rtrim(rtrim(number_format($comp['raw'], 2, '.', ''), '0'), '.') : '—' }}
                                                </td>

                                                {{-- Total Indikator --}}
                                                @if($k === 0)
                                                    <td rowspan="{{ $indRowCount }}" class="text-end fw-semibold align-middle"
                                                        style="background:#f8f9fa; {{ $indHasScore ? ($iR['total'] >= 70 ? 'color:#16a34a;' : 'color:#dc2626;') : 'color:#9ca3af;' }}">
                                                        @if($indHasScore)
                                                            {{ number_format($iR['total'], 1) }}
                                                            <div class="fw-normal" style="font-size:.65rem; color:#9ca3af;">× {{ $iR['weight'] }}%</div>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                @endif

                                                {{-- Total CPMK --}}
                                                @if($j === 0 && $k === 0)
                                                    <td rowspan="{{ $cpmkRowCount }}" class="text-end fw-bold align-middle"
                                                        style="{{ $cR['total'] > 0    ? ($cpmkLulus        ? 'background:#f0fdf4; color:#15803d; border-left:3px solid #22c55e;'        : 'background:#fef2f2; color:#dc2626; border-left:3px solid #ef4444;')    : 'background:#f8fafc; color:#9ca3af;' }}">
                                                        @if($cR['total'] > 0)
                                                            {{ number_format($cR['total'], 1) }}
                                                            <div style="font-size:.7rem; margin-top:2px;">
                                                                <span class="badge border" style="font-size:.6rem; background:{{ $cpmkLulus ? '#f0fdf4' : '#fef2f2' }}; color:{{ $cpmkLulus ? '#16a34a' : '#dc2626' }}; border-color:{{ $cpmkLulus ? '#bbf7d0' : '#fecaca' }} !important;">
                                                                    {{ $cpmkLulus ? '✓ Lulus' : '✗ Gagal' }}
                                                                </span>
                                                            </div>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Nilai Akhir --}}
                        <tr style="background:#fafafa;">
                            <td colspan="4" class="text-center fw-bold" style="font-size:1.05rem;">Nilai Akhir</td>
                            <td colspan="2" class="text-end">
                                <div class="fw-bold" style="font-size:1.6rem; line-height:1; color:{{ $anyFailed ? 'var(--obe-red)' : 'var(--obe-ink)' }};">
                                    {{ $finalGrade }}
                                </div>
                                <div class="small text-muted mt-1">
                                    {{ $anyFailed ? '0.0' : number_format($finalScore, 1) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Skala nilai konvensional --}}
    <div class="d-flex flex-wrap gap-2 mt-3 small">
        @foreach(['A ≥ 85','A- ≥ 80','B+ ≥ 75','B ≥ 70','B- ≥ 65','C+ ≥ 60','C ≥ 55','D ≥ 45','E < 45'] as $g)
            <span class="badge bg-light text-muted border" style="font-weight:500;">{{ $g }}</span>
        @endforeach
    </div>

</x-sidebar-layout>