<x-sidebar-layout :title="'Transkrip Nilai'" :header="'Transkrip Nilai'">

    <div class="no-print">

        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
            <p class="text-muted small mb-0">
                {{ $user->name }} — <span style="font-family:monospace;">{{ $user->identity }}</span>
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('mahasiswa.transkrip', ['mode'=>'konvensional']) }}"
                   class="btn btn-sm {{ $mode === 'konvensional' ? 'btn-obe-red' : 'btn-obe-outline' }}">
                   Konvensional
                </a>
                <a href="{{ route('mahasiswa.transkrip', ['mode'=>'obe']) }}"
                   class="btn btn-sm {{ $mode === 'obe' ? 'btn-obe-red' : 'btn-obe-outline' }}">
                   OBE (CPL)
                </a>
                @if($mode === 'obe')
                    <a href="{{ route('mahasiswa.transkrip.download.obe') }}"
                       class="btn btn-sm btn-obe-red d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Transkrip OBE
                    </a>
                @else
                    <a href="{{ route('mahasiswa.transkrip.download.konvensional') }}"
                       class="btn btn-sm btn-obe-red d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Transkrip
                    </a>
                @endif
            </div>
        </div>

        @if(count($transcriptRows) === 0)
            <div class="obe-card text-center py-5 text-muted">
                <em>Belum ada data nilai. Daftar ke kelas terlebih dahulu.</em>
            </div>

        @elseif($mode === 'konvensional')
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-4">
                    <div class="obe-stat-card">
                        <div class="obe-stat-card__label">Total SKS</div>
                        <div class="obe-stat-card__value">{{ $totalSks }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="obe-stat-card">
                        <div class="obe-stat-card__label">IPK</div>
                        <div class="obe-stat-card__value"
                             style="color:{{ $ipk >= 3.0 ? '#16a34a' : ($ipk >= 2.0 ? '#d97706' : 'var(--obe-red)') }};">
                            {{ number_format($ipk, 2) }}
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="obe-stat-card">
                        <div class="obe-stat-card__label">Mata Kuliah</div>
                        <div class="obe-stat-card__value">{{ count($transcriptRows) }}</div>
                    </div>
                </div>
            </div>

            <div class="obe-card p-0 overflow-hidden">
                <div class="px-3 py-3 border-bottom d-flex flex-wrap justify-content-between align-items-start gap-2"
                     style="background:var(--obe-bg);">
                    <div>
                        <h2 class="obe-card__title mb-1">Daftar Nilai Transkrip</h2>
                        <small class="text-muted">Rekapitulasi seluruh nilai mata kuliah yang telah ditempuh</small>
                    </div>
                    <a href="{{ route('mahasiswa.transkrip.download.konvensional') }}"
                       class="btn btn-sm btn-obe-outline d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Transkrip
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8fafc;">
                            <tr style="color:#64748b; font-weight:600; font-size:.78rem; text-transform:uppercase; letter-spacing:.04em;">
                                <th class="text-center" style="width:50px;">No</th>
                                <th style="width:120px;">Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th class="text-center" style="width:60px;">SKS</th>
                                <th class="text-center" style="width:60px;">W/P</th>
                                <th style="width:140px;">Sem. Ambil</th>
                                <th class="text-center" style="width:70px;">Nilai</th>
                                <th class="text-center" style="width:80px;">Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transcriptRows as $i => $row)
                                @php
                                    $lulus = $row['final_lulus'] && !$row['any_failed'];
                                    $wp    = $row['course']->wajib_pilihan ?? 'W';
                                    $grade = $row['final_grade'];
                                    $isGood      = in_array($grade, ['A','A-','B+','B','B-']);
                                    $badgeBg     = $isGood ? '#dcfce7' : ($grade === 'E' ? '#fee2e2' : '#fef3c7');
                                    $badgeColor  = $isGood ? '#15803d' : ($grade === 'E' ? '#b91c1c' : '#a16207');
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <span class="badge"
                                              style="background:#f1f5f9; color:#475569; font-family:monospace; font-weight:600; padding:.4rem .6rem;">
                                            {{ $row['course']->code }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('mahasiswa.classrooms.show', $row['classroom']) }}"
                                           class="fw-bold text-decoration-none" style="color:#0f172a;">
                                            {{ $row['course']->name }}
                                        </a>
                                        <div class="text-muted small"
                                             style="text-transform:uppercase; letter-spacing:.03em; font-size:.7rem;">
                                            {{ $row['classroom']->name }}
                                        </div>
                                        @if($row['any_failed'])
                                            <div>
                                                <span class="badge"
                                                      style="background:#fee2e2; color:#b91c1c; font-size:.65rem;">
                                                    Ada CPMK Gagal → E
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $row['course']->sks ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge"
                                              style="background:{{ $wp === 'W' ? '#dbeafe' : '#dcfce7' }};
                                                     color:{{ $wp === 'W' ? '#1d4ed8' : '#15803d' }};
                                                     padding:.35rem .55rem; font-weight:700;">
                                            {{ $wp }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ ucfirst($row['classroom']->period_type ?? '') }}
                                        @php $ay = $row['classroom']->academic_year ?? ''; @endphp
                                        @if($ay) {{ explode('/', $ay)[0] }} @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="d-inline-flex align-items-center justify-content-center fw-bold"
                                              style="min-width:36px; height:32px; padding:0 .55rem; border-radius:6px;
                                                     background:{{ $badgeBg }}; color:{{ $badgeColor }};">
                                            {{ $grade }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold" style="color:#0f172a;">
                                        {{ number_format($row['final_mutu'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--obe-bg);">
                                <td colspan="3" class="fw-bold text-end">Total SKS / IPK</td>
                                <td class="text-center fw-bold">{{ $totalSks }}</td>
                                <td colspan="3" class="text-end fw-semibold text-muted small">IPK</td>
                                <td class="text-center fw-bold" style="color:var(--obe-red);">
                                    {{ number_format($ipk, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-1 mt-3 small">
                @foreach(['A=4.00 (≥85)','A-=3.75 (≥80)','B+=3.50 (≥75)','B=3.00 (≥70)','B-=2.75 (≥65)','C+=2.50 (≥60)','C=2.00 (≥55)','D=1.00 (≥45)','E=0.00 (<45)'] as $g)
                    <span class="badge bg-light text-muted border">{{ $g }}</span>
                @endforeach
            </div>

        @else
            {{-- ── OBE / CPL (tampilan layar) ── --}}
            @php
                $cplTercapai = collect($cplAchievement)->filter(fn($r) => $r['average'] !== null && $r['average'] >= ($r['min_target'] ?? 70))->count();
                $cplTotal    = collect($cplAchievement)->count();
                $cplBelum    = collect($cplAchievement)->filter(fn($r) => $r['average'] !== null && $r['average'] < ($r['min_target'] ?? 70))->count();
            @endphp

            <div class="obe-card p-0 overflow-hidden mb-3">
                <div class="px-3 py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2"
                     style="background:var(--obe-bg);">
                    <div>
                        <h2 class="obe-card__title mb-0">Ketercapaian CPL</h2>
                        <small class="text-muted">
                            {{ $cplTotal }} CPL &bull;
                            <span style="color:#16a34a;">{{ $cplTercapai }} tercapai</span>
                            @if($cplBelum > 0)
                                &bull; <span style="color:#dc2626;">{{ $cplBelum }} belum tercapai</span>
                            @endif
                        </small>
                    </div>
                    <a href="{{ route('mahasiswa.transkrip.download.obe') }}"
                       class="btn btn-sm btn-obe-outline d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Transkrip OBE
                    </a>
                    <div class="d-lg-none small text-muted fst-italic">Geser ke kanan untuk lihat semua CPL.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0"
                           style="font-size:.8rem; min-width: {{ 200 + count($cplAchievement) * 90 }}px;">
                        <thead>
                            <tr style="background:var(--obe-ink);">
                                @foreach($cplAchievement as $row)
                                    <th class="text-center"
                                        style="min-width:85px; background:var(--obe-ink); color:#fff;"
                                        title="{{ $row['cpl']->description }}">
                                        {{ $row['cpl']->code }}
                                        @if($row['min_target'])
                                            <div class="fw-normal" style="font-size:.6rem; color:#9ca3af;">
                                                min {{ rtrim(rtrim(number_format($row['min_target'], 2, '.', ''), '0'), '.') }}%
                                            </div>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach($cplAchievement as $row)
                                    @php
                                        $avg       = $row['average'];
                                        $minTarget = $row['min_target'] ?? 70;
                                        $lulus     = $avg !== null && $avg >= $minTarget;
                                        $bgColor   = $avg === null ? '' : ($lulus ? 'background:#dcfce7;' : 'background:#fee2e2;');
                                        $txtColor  = $avg === null ? 'color:#9ca3af;' : ($lulus ? 'color:#166534;' : 'color:#b91c1c;');
                                    @endphp
                                    <td class="text-center fw-bold" style="{{ $bgColor }}{{ $txtColor }}">
                                        @if($avg !== null)
                                            {{ number_format($avg, 1) }}%
                                        @else
                                            <span class="text-muted fw-normal">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="obe-card p-0 overflow-hidden">
                <div class="px-3 py-3 border-bottom" style="background:var(--obe-bg);">
                    <h2 class="obe-card__title mb-1">Detail Ketercapaian CPL</h2>
                    <small class="text-muted">Akumulasi ketercapaian CPL dari seluruh mata kuliah yang telah ditempuh.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size:.85rem;">
                        <thead style="background:var(--obe-ink); color:#fff;">
                            <tr>
                                <th style="width:110px; background:var(--obe-ink); color:#fff;" class="text-center">Kode CPL</th>
                                <th style="background:var(--obe-ink); color:#fff;">Pernyataan CPL</th>
                                <th style="width:120px; background:var(--obe-ink); color:#fff;" class="text-center">Target Min.</th>
                                <th style="width:200px; background:var(--obe-ink); color:#fff;" class="text-center">Ketercapaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cplAchievement as $row)
                                @php
                                    $avg       = $row['average'];
                                    $minTarget = $row['min_target'] ?? 70;
                                    $lulus     = $avg !== null && $avg >= $minTarget;
                                    $pct       = $avg !== null ? min($avg, 100) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <span class="badge fw-bold"
                                              style="font-family:monospace; font-size:.82rem; padding:.4rem .7rem;
                                                     background:{{ $avg === null ? '#f3f4f6' : ($lulus ? '#f0fdf4' : '#fef2f2') }};
                                                     color:{{ $avg === null ? '#6b7280' : ($lulus ? '#15803d' : '#dc2626') }};
                                                     border:1px solid {{ $avg === null ? '#e5e7eb' : ($lulus ? '#bbf7d0' : '#fecaca') }};
                                                     border-left:3px solid {{ $avg === null ? '#d1d5db' : ($lulus ? '#22c55e' : '#ef4444') }};">
                                            {{ $row['cpl']->code }}
                                        </span>
                                    </td>
                                    <td style="font-size:.82rem;">{{ $row['cpl']->description ?? '—' }}</td>
                                    <td class="text-center text-muted small">
                                        {{ rtrim(rtrim(number_format($minTarget, 2, '.', ''), '0'), '.') }}%
                                    </td>
                                    <td class="text-center">
                                        @if($avg === null)
                                            <span class="text-muted small fst-italic">Belum ada nilai</span>
                                        @else
                                            <div class="d-flex align-items-center gap-2 justify-content-center">
                                                <div class="flex-grow-1 rounded-pill overflow-hidden"
                                                     style="height:8px; background:#f3f4f6; max-width:80px;">
                                                    <div class="rounded-pill"
                                                         style="height:100%; width:{{ $pct }}%;
                                                                background:{{ $lulus ? '#22c55e' : '#ef4444' }};
                                                                transition:width .3s;"></div>
                                                </div>
                                                <span class="fw-bold"
                                                      style="min-width:46px; color:{{ $lulus ? '#15803d' : '#dc2626' }};">
                                                    {{ number_format($avg, 1) }}%
                                                </span>
                                                <span style="font-size:.7rem; color:{{ $lulus ? '#15803d' : '#dc2626' }};">
                                                    {{ $lulus ? '✓' : '✗' }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <em>Belum ada data ketercapaian CPL.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>{{-- .no-print --}}

</x-sidebar-layout>