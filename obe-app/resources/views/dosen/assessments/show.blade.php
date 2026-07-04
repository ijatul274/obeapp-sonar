<x-sidebar-layout :title="'Input Nilai'" :header="$assessment->name">

    @php
        $cpmk = $assessment->indicator->cpmk;
        $course = $cpmk->course;
        $classroom = $course->classrooms->first();
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <p class="text-muted small mb-0">
            {{ $course->name }} · <span class="fw-semibold">{{ $cpmk->code }}</span>
            @if($cpmk->cpl) · <span class="badge bg-light text-dark border">{{ $cpmk->cpl->code }}</span>@endif
        </p>
        @if($classroom)
            <a href="{{ route('dosen.classrooms.show', $classroom) }}" class="btn btn-obe-outline btn-sm">&larr; Kembali</a>
        @else
            <a href="{{ route('dosen.dashboard') }}" class="btn btn-obe-outline btn-sm">&larr; Dashboard</a>
        @endif
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Komponen</div>
                <div class="obe-stat-card__value" style="font-size:1rem;">{{ $assessment->name }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Bobot</div>
                <div class="obe-stat-card__value">{{ number_format($assessment->percentage, 1) }}%</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Mahasiswa</div>
                <div class="obe-stat-card__value">{{ $students->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="obe-stat-card">
                <div class="obe-stat-card__label">Sudah Dinilai</div>
                <div class="obe-stat-card__value">
                    {{ $scores->count() }}<small class="text-muted fw-normal" style="font-size:.85rem;">/{{ $students->count() }}</small>
                </div>
            </div>
        </div>
    </div>

    @if($students->count() > 0)
        @php $pct = $students->count() > 0 ? ($scores->count() / $students->count()) * 100 : 0; @endphp
        <div class="progress mb-3" style="height:6px;">
            <div class="progress-bar" role="progressbar" style="width:{{ $pct }}%; background:var(--obe-red);" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    @endif

    <div class="obe-card p-0 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom" style="background:var(--obe-bg);">
            <h2 class="obe-card__title mb-0">Daftar Nilai Mahasiswa</h2>
            @if($assessment->description)
                <small class="text-muted fst-italic d-none d-sm-inline">{{ $assessment->description }}</small>
            @endif
        </div>

        <form action="{{ route('assessments.scores.store', $assessment) }}" method="POST">
            @csrf

            @if($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:50px;">#</th>
                                <th style="width:160px;">NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th class="text-center" style="width:140px;">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $idx => $st)
                                @php $existing = $scores[$st->id] ?? null; @endphp
                                <tr>
                                    <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                    <td><span class="badge bg-light text-dark border" style="font-family:monospace;">{{ $st->identity ?? '-' }}</span></td>
                                    <td class="fw-semibold">{{ $st->name }}</td>
                                    <td class="text-center">
                                        <input type="number" name="scores[{{ $st->id }}]" value="{{ $existing ?? '' }}" min="0" max="100" step="0.01"
                                               class="form-control form-control-sm text-center mx-auto fw-bold" style="max-width:90px; {{ $existing !== null ? 'background:var(--obe-red-soft); border-color:var(--obe-red);' : '' }}" placeholder="—">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top" style="background:var(--obe-bg);">
                    <small class="text-muted">
                        <strong>{{ $scores->count() }}</strong> dari <strong>{{ $students->count() }}</strong> mahasiswa sudah dinilai
                    </small>
                    <button type="submit" class="btn btn-obe-red d-inline-flex align-items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Simpan Nilai
                    </button>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted fw-semibold mb-1">Belum ada mahasiswa terdaftar.</p>
                    <small class="text-muted">Mahasiswa perlu melakukan enrollment ke kelas terlebih dahulu.</small>
                </div>
            @endif
        </form>
    </div>

</x-sidebar-layout>
