<x-sidebar-layout :title="'Detail ' . $student->name" :header="'Laporan Nilai'">

    @include('kaprodi.laporan._subnav')

    {{-- Tombol kembali + info mahasiswa --}}
    <div class="mb-3">
        <a href="{{ route('kaprodi.laporan.mahasiswa') }}" class="btn btn-obe-outline btn-sm mb-3">
            &larr; Kembali ke Daftar Mahasiswa
        </a>

        @php
            $nim      = $student->profilMahasiswa?->nim ?? $student->identity ?? '-';
            $prefix   = substr($nim, 0, 2);
            $angkatan = is_numeric($prefix) ? '20'.$prefix : null;
            $prodi    = $student->profilMahasiswa?->programStudi?->name ?? null;
        @endphp

        <div class="obe-card p-3 d-flex flex-wrap align-items-center gap-3">
            {{-- Avatar --}}
            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle flex-shrink-0"
                 style="width:48px; height:48px; background:var(--obe-red); font-size:1.1rem;">
                {{ strtoupper(substr($student->name, 0, 1)) }}
            </div>
            <div class="flex-grow-1">
                <h2 class="h5 fw-bold mb-0">{{ $student->name }}</h2>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    <span class="badge bg-light text-dark border" style="font-family:monospace; font-size:.75rem;">{{ $nim }}</span>
                    @if($angkatan)
                        <span class="badge bg-light text-dark border" style="font-size:.75rem;">Angkatan {{ $angkatan }}</span>
                    @endif
                    @if($prodi)
                        <span class="badge bg-light text-dark border" style="font-size:.75rem;">{{ $prodi }}</span>
                    @endif
                </div>
            </div>
            {{-- Stat ringkas --}}
            <div class="d-flex gap-3 text-center">
                <div>
                    <div class="fw-bold" style="font-size:1.3rem; color:var(--obe-red);">{{ $classrooms->count() }}</div>
                    <div class="text-muted" style="font-size:.7rem;">Kelas</div>
                </div>
                <div style="width:1px; background:var(--obe-line);"></div>
                <div>
                    @php
                        $lulusCount = collect($classroomResults)->filter(fn($r) => $r && $r['finalScore'] !== null && !$r['anyFailed'])->count();
                    @endphp
                    <div class="fw-bold" style="font-size:1.3rem; color:#16a34a;">{{ $lulusCount }}</div>
                    <div class="text-muted" style="font-size:.7rem;">Lulus</div>
                </div>
                <div style="width:1px; background:var(--obe-line);"></div>
                <div>
                    @php
                        $gagalCount = collect($classroomResults)->filter(fn($r) => $r && $r['anyFailed'])->count();
                    @endphp
                    <div class="fw-bold" style="font-size:1.3rem; color:{{ $gagalCount > 0 ? '#dc2626' : '#9ca3af' }};">{{ $gagalCount }}</div>
                    <div class="text-muted" style="font-size:.7rem;">Gagal</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Kolom kiri: Daftar Kelas --}}
        <div class="{{ $cpls->isNotEmpty() ? 'col-lg-8' : 'col-12' }}">
            <div class="obe-card p-0 overflow-hidden h-100">
                <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between"
                     style="background:var(--obe-bg);">
                    <div>
                        <h3 class="obe-card__title mb-0">Daftar Kelas</h3>
                        <small class="text-muted">{{ $classrooms->count() }} kelas terdaftar</small>
                    </div>
                </div>

                @if($classrooms->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <p class="mb-0 fw-semibold">Belum ada kelas yang diikuti</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:.82rem;">
                            <thead style="background:var(--obe-ink); color:#fff;">
                                <tr>
                                    <th style="min-width:180px;">Mata Kuliah</th>
                                    <th class="text-center" style="width:100px;">Periode</th>
                                    <th class="text-center" style="width:60px;">SKS</th>
                                    <th>CPMK</th>
                                    <th class="text-center" style="width:90px;">Nilai</th>
                                    <th class="text-center" style="width:70px;">Laporan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classrooms as $classroom)
                                    @php $res = $classroomResults[$classroom->id] ?? null; @endphp
                                    <tr style="{{ ($res && $res['anyFailed']) ? 'background:#fef2f2;' : '' }}">
                                        <td>
                                            <div class="fw-semibold">{{ $classroom->course?->name ?? '-' }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">
                                                {{ $classroom->course?->code }} &bull; {{ $classroom->name }}
                                            </div>
                                        </td>
                                        <td class="text-center small">
                                            <div class="fw-semibold">{{ ucfirst($classroom->period_type ?? '-') }}</div>
                                            <div class="text-muted" style="font-size:.7rem;">{{ $classroom->academic_year ?? '' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $classroom->course?->sks ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if($res && !empty($res['cpmkResults']))
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($res['cpmkResults'] as $cr)
                                                        @if($cr['score'] !== null)
                                                            <span class="badge border"
                                                                  style="font-size:.65rem; font-family:monospace; padding:.3rem .5rem;
                                                                         background:{{ $cr['lulus'] ? '#f0fdf4' : '#fef2f2' }};
                                                                         color:{{ $cr['lulus'] ? '#15803d' : '#dc2626' }};
                                                                         border-color:{{ $cr['lulus'] ? '#bbf7d0' : '#fecaca' }} !important;
                                                                         border-left:3px solid {{ $cr['lulus'] ? '#22c55e' : '#ef4444' }} !important;"
                                                                  title="{{ $cr['cpmk']->code }}: {{ number_format($cr['score'], 1) }} — {{ $cr['lulus'] ? 'Tercapai ✓' : 'Belum tercapai ✗ (min 70)' }}">
                                                                {{ $cr['cpmk']->code }} {{ number_format($cr['score'], 1) }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-muted border" style="font-size:.65rem; font-family:monospace; padding:.3rem .5rem;"
                                                                  title="{{ $cr['cpmk']->code }}: belum ada nilai">
                                                                {{ $cr['cpmk']->code }} —
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic small">Belum ada nilai</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($res && $res['finalScore'] !== null)
                                                <span class="d-inline-flex align-items-center justify-content-center fw-bold"
                                                      style="width:32px; height:32px; border-radius:6px; font-size:.85rem;
                                                             background:{{ $res['anyFailed'] ? '#fee2e2' : '#dcfce7' }};
                                                             color:{{ $res['anyFailed'] ? '#dc2626' : '#15803d' }};">
                                                    {{ $res['finalGrade'] }}
                                                </span>
                                                <div style="font-size:.65rem; color:#9ca3af; margin-top:1px;">
                                                    {{ number_format($res['finalMutu'], 2) }}
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('kaprodi.laporan.mahasiswa.kelas', [$student, $classroom]) }}"
                                               class="btn btn-sm btn-obe-outline py-0 px-2" style="font-size:.75rem;">Lihat</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Legenda --}}
                    <div class="px-3 py-2 border-top d-flex flex-wrap gap-3 align-items-center"
                         style="background:var(--obe-bg); font-size:.72rem;">
                        <span class="text-muted fw-semibold text-uppercase" style="font-size:.62rem; letter-spacing:.05em;">Keterangan:</span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <span style="width:12px; height:12px; background:#dcfce7; border:1px solid #bbf7d0; border-radius:2px; display:inline-block;"></span>
                            <span class="text-muted">Lulus</span>
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <span style="width:12px; height:12px; background:#fee2e2; border:1px solid #fecaca; border-radius:2px; display:inline-block;"></span>
                            <span class="text-muted">Ada CPMK gagal → nilai E</span>
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom kanan: Ringkasan CPL --}}
        @if($cpls->isNotEmpty())
        <div class="col-lg-4">
            <div class="obe-card p-0 overflow-hidden">
                <div class="px-3 py-2 border-bottom" style="background:var(--obe-bg);">
                    <h3 class="obe-card__title mb-0">Ketercapaian CPL</h3>
                    <small class="text-muted">Rata-rata seluruh kelas</small>
                </div>
                <div class="p-3 d-flex flex-column gap-2">
                    @foreach($cpls as $cpl)
                        @php
                            $val       = $cplSummary[$cpl->id] ?? null;
                            $minTarget = (float) ($cpl->min_target ?? 70);
                            $lulus     = $val !== null && $val >= $minTarget;
                            $pct       = $val !== null ? min($val, 100) : 0;
                        @endphp
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold" style="font-family:monospace; font-size:.82rem; color:var(--obe-red);">{{ $cpl->code }}</span>
                                    @if($cpl->description)
                                        <span class="text-muted" style="font-size:.72rem; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $cpl->description }}">{{ $cpl->description }}</span>
                                    @endif
                                </div>
                                <span class="fw-bold" style="font-size:.82rem; color:{{ $val === null ? '#9ca3af' : ($lulus ? '#16a34a' : '#dc2626') }};">
                                    {{ $val !== null ? number_format($val, 1).'%' : '—' }}
                                </span>
                            </div>
                            {{-- Progress bar --}}
                            <div class="rounded-pill overflow-hidden" style="height:6px; background:#f3f4f6;">
                                <div class="rounded-pill" style="height:100%; width:{{ $pct }}%;
                                     background:{{ $val === null ? '#e5e7eb' : ($lulus ? '#22c55e' : '#ef4444') }};
                                     transition:width .3s;"></div>
                            </div>
                            @if($cpl->min_target)
                                <div class="text-muted" style="font-size:.62rem; margin-top:2px;">
                                    Target min {{ $cpl->min_target }}%
                                    @if($val !== null)
                                        &bull; <span style="color:{{ $lulus ? '#16a34a' : '#dc2626' }};">{{ $lulus ? '✓ Tercapai' : '✗ Belum tercapai' }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

</x-sidebar-layout>