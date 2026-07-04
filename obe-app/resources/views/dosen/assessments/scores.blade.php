<x-sidebar-layout :title="'Input Nilai'" :header="$assessment->name">

    @php
        $indicator = $assessment->indicator;
        $cpmk = $indicator->cpmk;
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <p class="text-muted small mb-0">
            <span class="fw-semibold">{{ $cpmk->code }}</span> ·
            Indikator: {{ $indicator->description ? Str::limit($indicator->description, 50) : '-' }} · {{ $classroom->name }}
        </p>
        <a href="{{ route('dosen.classrooms.show', $classroom) }}" class="btn btn-obe-outline btn-sm">&larr; Kembali</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Komponen</div>
                <div class="obe-stat-card__value" style="font-size:1rem;">{{ $assessment->name }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Bobot</div>
                <div class="obe-stat-card__value">{{ number_format($assessment->percentage, 1) }}%</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Mahasiswa</div>
                <div class="obe-stat-card__value">{{ $students->count() }}</div>
            </div>
        </div>
    </div>

    <div class="obe-card p-0 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom" style="background:var(--obe-bg);">
            <div>
                <h2 class="obe-card__title mb-0">Daftar Nilai Mahasiswa</h2>
                <small class="text-muted">Isi nilai 0–100 untuk setiap mahasiswa.</small>
            </div>
            <small class="text-muted">Minimal kelulusan CPMK: <strong style="color:var(--obe-red);">70</strong></small>
        </div>

        <form method="POST" action="{{ route('dosen.assessments.scores.store', $assessment) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">No</th>
                            <th style="width:130px;">NIM</th>
                            <th>Nama</th>
                            <th class="text-center" style="width:140px;">Nilai (0–100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $st)
                            @php $existing = $scoreMap[$st->id]?->score ?? ''; @endphp
                            <input type="hidden" name="scores[{{ $i }}][student_id]" value="{{ $st->id }}">
                            <tr>
                                <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                <td class="small" style="font-family:monospace;">{{ $st->identity ?? '-' }}</td>
                                <td class="fw-semibold">{{ $st->name }}</td>
                                <td class="text-center">
                                    <input type="number" name="scores[{{ $i }}][score]" value="{{ $existing }}"
                                           min="0" max="100" step="0.01" placeholder="—"
                                           class="form-control form-control-sm text-center mx-auto fw-bold"
                                           style="max-width:90px; {{ $existing !== '' && $existing < 70 ? 'color:var(--obe-red); border-color:var(--obe-red);' : '' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top" style="background:var(--obe-bg);">
                <small class="text-muted">Nilai &lt; 70 → CPMK tidak lulus → nilai MK otomatis E.</small>
                <button type="submit" class="btn btn-obe-red d-inline-flex align-items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Semua Nilai
                </button>
            </div>
        </form>
    </div>

</x-sidebar-layout>
