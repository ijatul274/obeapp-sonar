<x-sidebar-layout :title="'Dashboard Dosen'" :header="'Daftar Kelas'">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">
            Periode aktif:
            <span class="fw-semibold" style="color:var(--obe-red);">{{ ucfirst($activePeriod['period_type']) }} {{ $activePeriod['academic_year'] }}</span>
        </p>
    </div>

    @if($classrooms->isEmpty())
        <div class="alert alert-warning">
            Anda belum ditugaskan pada CPMK kelas apapun oleh Kaprodi untuk periode aktif ini.
        </div>
    @else
        <div class="obe-card p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 obe-dt"
                       data-no-sort="0,8"
                       data-page-length="50">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">No</th>
                            <th>Nama Kelas</th>
                            <th>Mata Kuliah</th>
                            <th class="text-center" style="width:60px;">SKS</th>
                            <th class="text-center" style="width:60px;">Sem</th>
                            <th>CPMK yang Ditugaskan</th>
                            <th class="text-center" style="width:160px;">Pertemuan</th>
                            <th class="text-center" style="width:160px;">Kode Kelas</th>
                            <th class="text-center" style="width:160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classrooms as $i => $c)
                            @php
                                $assignedCpmks = $c->cpmks;
                                $starts = $assignedCpmks->pluck('meeting_start')->filter()->map(fn($v)=>(int)$v);
                                $ends   = $assignedCpmks->pluck('meeting_end')->filter()->map(fn($v)=>(int)$v);
                                $pertLabel = ($starts->isNotEmpty() && $ends->isNotEmpty())
                                    ? 'Pertemuan ' . $starts->min() . '–' . $ends->max()
                                    : '—';
                            @endphp
                            <tr>
                                <td class="text-center text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $c->name }}</div>
                                    <div class="text-muted small">{{ $c->academic_year ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ $c->course?->name ?? '-' }}</div>
                                    <div class="text-muted" style="font-size:.72rem; font-family:monospace;">{{ $c->course?->code }}</div>
                                </td>
                                <td class="text-center">{{ $c->course?->sks ?? '-' }}</td>
                                <td class="text-center">{{ $c->course?->semester ?? '-' }}</td>
                                <td>
                                    @if($assignedCpmks->isEmpty())
                                        <span class="text-muted small">—</span>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($assignedCpmks as $cpmk)
                                                <span class="badge bg-light text-dark border" title="{{ $cpmk->name ?? '' }}">{{ $cpmk->code ?? $cpmk->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $pertLabel }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge" style="background:#fee2e2; color:#b91c1c; font-family:monospace;">{{ $c->enrollment_code }}</span>
                                    <button type="button" class="btn btn-sm p-0 ms-1 border-0 bg-transparent text-muted"
                                            onclick="navigator.clipboard.writeText('{{ $c->enrollment_code }}'); this.innerText='✓'; setTimeout(()=>this.innerText='⎘',1500);">⎘</button>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('dosen.classrooms.show', $c) }}" class="btn btn-sm btn-obe-outline">Detail</a>
                                        <a href="{{ route('dosen.classrooms.report', $c) }}" class="btn btn-sm btn-obe-outline">Laporan</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-sidebar-layout>
