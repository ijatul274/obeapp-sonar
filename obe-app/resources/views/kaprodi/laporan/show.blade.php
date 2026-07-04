<x-sidebar-layout :title="'Laporan ' . $classroom->name" :header="'Laporan Nilai: ' . $classroom->name">

    @include('kaprodi.laporan._subnav')

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">
            {{ $course?->code }} — {{ $course?->name }} · {{ ucfirst($classroom->period_type ?? '') }} {{ $classroom->academic_year ?? '' }}
        </p>
        <div class="d-flex gap-2">
            <a href="{{ route('kaprodi.laporan.index') }}" class="btn btn-obe-outline btn-sm">&larr; Kembali</a>
            <button type="button" class="btn btn-obe-red btn-sm" data-bs-toggle="modal" data-bs-target="#convertExcelModal">
                Convert to Excel
            </button>
        </div>
    </div>

    @if($cpmks->isEmpty())
        <div class="obe-card text-center py-5">
            <p class="fw-semibold text-muted mb-1">Belum ada CPMK yang disetujui untuk kelas ini.</p>
            <small class="text-muted d-block mb-3">Dosen perlu membuat dan mengajukan CPMK terlebih dahulu.</small>
        </div>
    @else
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($cpmks as $cpmk)
                <div class="d-flex align-items-center gap-2 border rounded px-2 py-1 bg-white small">
                    <span class="fw-bold" style="font-family:monospace; color:var(--obe-red);">{{ $cpmk->code }}</span>
                    @if($cpmk->cpl)<span class="badge bg-light text-dark border">{{ $cpmk->cpl->code }}</span>@endif
                    <span class="text-muted">{{ $cpmk->percentage }}%</span>
                </div>
            @endforeach
        </div>

        <div class="obe-card p-0 overflow-hidden">
            <div class="px-3 py-3 border-bottom" style="background:var(--obe-bg);">
                <h2 class="obe-card__title mb-1">Rekap Nilai Mahasiswa</h2>
                <small class="text-muted">Minimal CPMK lulus: 70 · Nilai otomatis E jika ada CPMK gagal</small>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size:.78rem;">
                    <thead style="background:var(--obe-ink); color:#fff;">
                        <tr>
                            <th rowspan="2" class="text-center" style="background:var(--obe-ink); color:#fff;">No</th>
                            <th rowspan="2" style="background:var(--obe-ink); color:#fff;">NIM</th>
                            <th rowspan="2" style="background:var(--obe-ink); color:#fff;">Nama</th>
                            @foreach($cpmks as $cpmk)
                                <th colspan="{{ $cpmk->indicators->count() + 1 }}" class="text-center" style="background:var(--obe-ink); color:#fff;">
                                    {{ $cpmk->code }}
                                    <div class="small fw-normal" style="font-size:.65rem; color:#9ca3af;">{{ $cpmk->percentage }}%</div>
                                </th>
                            @endforeach
                            <th rowspan="2" class="text-center" style="background:var(--obe-ink); color:#fff;">Nilai</th>
                            <th rowspan="2" class="text-center" style="background:var(--obe-ink); color:#fff;">Mutu</th>
                            <th rowspan="2" class="text-center" style="background:var(--obe-ink); color:#fff;">Grade</th>
                        </tr>
                        <tr style="background:#374151; color:#d1d5db;">
                            @foreach($cpmks as $cpmk)
                                @foreach($cpmk->indicators as $ind)
                                    <th class="text-center" style="background:#374151; color:#d1d5db; font-size:.65rem;">
                                        Ind.{{ $loop->iteration }}<br><small style="color:#9ca3af;">{{ $ind->percentage }}%</small>
                                    </th>
                                @endforeach
                                <th class="text-center" style="background:#374151; color:#fbbf24; font-size:.65rem;">Total</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $row)
                            <tr style="{{ $row['any_failed'] ? 'background:#fef2f2;' : '' }}">
                                <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                <td style="font-family:monospace;" class="small">{{ $row['student']->identity ?? '-' }}</td>
                                <td class="fw-semibold">{{ $row['student']->name }}</td>

                                @foreach($row['cpmks'] as $cR)
                                    @foreach($cR['indicators'] as $iR)
                                        <td class="text-center small {{ ($iR['total'] !== null && $iR['total'] < 70) ? 'fw-bold' : '' }}"
                                            style="{{ ($iR['total'] !== null && $iR['total'] < 70) ? 'color:var(--obe-red);' : '' }}">
                                            {{ $iR['total'] !== null ? number_format($iR['total'], 1) : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold" style="color:{{ $cR['total'] < 70 ? 'var(--obe-red)' : '#16a34a' }};">
                                        {{ number_format($cR['total'], 1) }}
                                        <div style="font-size:.6rem;">{{ $cR['lulus'] ? '✓' : '✗' }}</div>
                                    </td>
                                @endforeach

                                <td class="text-center fw-bold" style="color:{{ $row['any_failed'] ? 'var(--obe-red)' : 'inherit' }};">
                                    {{ $row['any_failed'] ? '0' : number_format($row['final_score'], 1) }}
                                </td>
                                <td class="text-center fw-bold">{{ number_format($row['final_mutu'], 2) }}</td>
                                <td class="text-center">
                                    <span class="d-inline-flex align-items-center justify-content-center fw-bold"
                                          style="width:32px; height:32px; border-radius:6px;
                                                 background:{{ $row['any_failed'] ? '#fee2e2' : '#dcfce7' }};
                                                 color:{{ $row['any_failed'] ? '#dc2626' : '#15803d' }};">
                                        {{ $row['final_grade'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @include('partials._transkrip_obe', [
            'cplRows'  => $cplRows,
            'subtitle' => 'Ketercapaian CPL pada kelas ini (rata-rata nilai CPMK seluruh mahasiswa per CPL).',
        ])
    @endif

    {{-- Modal Convert to Excel (format SATU UNRI) --}}
    @php
        $b = $classroom->satu_unri_bobot ?? [];
        $bb = fn($k) => isset($b[$k]) ? rtrim(rtrim(number_format((float)$b[$k], 2, '.', ''), '0'), '.') : '0';
    @endphp
    <div class="modal fade" id="convertExcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('dosen.classrooms.export-satu-unri', $classroom) }}" id="formSatuUnri">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Convert to Excel — Format SATU UNRI</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Tentukan bobot persentase tiap kriteria penilaian. Total semua bobot harus <strong>100%</strong>.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:18%;">Kriteria</th>
                                        <th style="width:32%;">Bobot (%)</th>
                                        <th style="width:18%;">Kriteria</th>
                                        <th style="width:32%;">Bobot (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold">Partisipasi Aktif</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="partisipasi_aktif" value="{{ $bb('partisipasi_aktif') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                        <td class="fw-semibold">Proyek</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="proyek" value="{{ $bb('proyek') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Presensi</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="presensi" value="{{ $bb('presensi') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                        <td class="fw-semibold">Tugas</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="tugas" value="{{ $bb('tugas') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Kuis</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="kuis" value="{{ $bb('kuis') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                        <td class="fw-semibold">Praktikum</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="praktikum" value="{{ $bb('praktikum') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">UTS</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="uts" value="{{ $bb('uts') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                        <td class="fw-semibold">UAS</td>
                                        <td><input type="number" step="0.01" min="0" max="100" name="uas" value="{{ $bb('uas') }}" class="form-control form-control-sm satu-bobot-input" required></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="text-end fw-bold" colspan="3">Total</td>
                                        <td>
                                            <span id="satuTotalBadge" class="badge bg-light text-dark border" style="font-size:.9rem;">0%</span>
                                            <small id="satuTotalHint" class="ms-2 text-danger d-none">Total harus 100%</small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red d-inline-flex align-items-center gap-2" id="satuSubmitBtn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                            Simpan Bobot &amp; Convert to Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const inputs = document.querySelectorAll('.satu-bobot-input');
            const badge  = document.getElementById('satuTotalBadge');
            const hint   = document.getElementById('satuTotalHint');
            const btn    = document.getElementById('satuSubmitBtn');
            function recalc() {
                let total = 0;
                inputs.forEach(i => total += parseFloat(i.value || 0));
                total = Math.round(total * 100) / 100;
                if (badge) badge.textContent = total + '%';
                const ok = total === 100;
                if (badge) {
                    badge.style.background = ok ? '#dcfce7' : '#fee2e2';
                    badge.style.color      = ok ? '#15803d' : '#b91c1c';
                }
                if (hint) hint.classList.toggle('d-none', ok);
                if (btn)  btn.disabled = !ok;
            }
            inputs.forEach(i => i.addEventListener('input', recalc));
            recalc();
        })();
    </script>

    <style>@media print { .obe-sidebar, .obe-topbar, .obe-footer, .btn { display:none !important; } .obe-content { padding:0 !important; } }</style>
</x-sidebar-layout>