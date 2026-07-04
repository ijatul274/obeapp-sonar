<x-sidebar-layout :title="'Dashboard Kaprodi'" :header="'Dashboard Kaprodi'">

    <div class="obe-card">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3 gap-2">
            <h2 class="obe-card__title">Pemetaan CPL dalam Mata Kuliah</h2>
            <span class="badge bg-light text-dark border">{{ $courses->count() }} MK &bull; {{ $cpls->count() }} CPL</span>
        </div>

        <div class="d-lg-none small text-muted fst-italic mb-2">Geser ke kanan untuk melihat lebih banyak CPL.</div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="font-size:.82rem;">
                <thead>
                    <tr style="background:var(--obe-ink); color:#fff;">
                        <th style="position:sticky; left:0; background:var(--obe-ink); min-width:200px;">Mata Kuliah</th>
                        <th style="min-width:120px; background:var(--obe-ink); color:#fff;">CPMK</th>
                        @foreach($cpls as $cpl)
                            <th class="text-center" title="{{ $cpl->description }}" style="min-width:60px; background:var(--obe-ink); color:#fff;">{{ $cpl->code }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php $currentSemester = null; @endphp
                    @forelse($courses as $course)
                        @if($course->semester !== $currentSemester)
                            @php $currentSemester = $course->semester; @endphp
                            <tr style="background:var(--obe-red-soft);">
                                <td colspan="{{ $cpls->count() + 2 }}" class="fw-bold text-uppercase small" style="color:var(--obe-red); letter-spacing:.05em;">
                                    Semester {{ $currentSemester }}
                                </td>
                            </tr>
                        @endif

                        @php $cpmkList = $course->cpmks; $rowSpan = max($cpmkList->count(), 1); @endphp

                        @if($cpmkList->isEmpty())
                            <tr>
                                <td style="position:sticky; left:0; background:#fff;">
                                    <div class="fw-bold">{{ $course->code }}</div>
                                    <div class="text-muted small">{{ $course->name }}</div>
                                </td>
                                <td class="text-muted fst-italic small">Belum ada CPMK</td>
                                @foreach($cpls as $cpl)
                                    <td class="text-center text-muted">–</td>
                                @endforeach
                            </tr>
                        @else
                            @foreach($cpmkList as $idx => $cpmk)
                                <tr>
                                    @if($idx === 0)
                                        <td rowspan="{{ $rowSpan }}" style="position:sticky; left:0; background:#fff; vertical-align:top;">
                                            <div class="fw-bold">{{ $course->code }}</div>
                                            <div class="text-muted small">{{ $course->name }}</div>
                                        </td>
                                    @endif
                                    <td>
                                        <span class="badge bg-light text-dark border" style="font-family:monospace;">{{ $cpmk->code }}</span>
                                    </td>
                                    @foreach($cpls as $cpl)
                                        @php $supported = $cpmk->cpl_id == $cpl->id; @endphp
                                        <td class="text-center" style="{{ $supported ? 'background:var(--obe-red-soft);' : '' }}">
                                            @if($supported)
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white"
                                                      style="width:24px; height:24px; background:var(--obe-red);" title="CPMK ini mendukung {{ $cpl->code }}">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            @else
                                                <span class="text-muted">–</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr><td colspan="{{ $cpls->count() + 2 }}" class="text-center text-muted py-4">Belum ada data mata kuliah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-sidebar-layout>
