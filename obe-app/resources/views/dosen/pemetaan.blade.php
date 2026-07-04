<x-sidebar-layout :title="'Dashboard'" :header="'Dashboard'">

    <div class="obe-card">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3 gap-2">
            <div>
                <h2 class="obe-card__title mb-0">Pemetaan CPL dalam Mata Kuliah</h2>
                <p class="text-muted small mb-0 mt-1">Hanya menampilkan mata kuliah yang Anda ampu.</p>
            </div>
            <span class="badge bg-light text-dark border">
                {{ $courses->count() }} MK &bull; {{ $cpls->count() }} CPL
            </span>
        </div>

        @if($courses->isEmpty())
            <div class="text-center py-5 text-muted">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3 d-block mx-auto" style="opacity:.35"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="mb-1 fw-semibold">Belum ada mata kuliah yang ditugaskan</p>
                <p class="small">Hubungi Kaprodi untuk mendapatkan penugasan kelas.</p>
            </div>
        @else
            <div class="d-lg-none small text-muted fst-italic mb-2">Geser ke kanan untuk melihat lebih banyak CPL.</div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" style="font-size:.82rem;">
                    <thead>
                        <tr style="background:var(--obe-ink); color:#fff;">
                            <th style="position:sticky; left:0; background:var(--obe-ink); min-width:200px;">Mata Kuliah</th>
                            <th style="min-width:110px; background:var(--obe-ink); color:#fff;">CPMK</th>
                            @if($cpls->isEmpty())
                                <th style="background:var(--obe-ink); color:#fff;">CPL</th>
                            @else
                                @foreach($cpls as $cpl)
                                    <th class="text-center" title="{{ $cpl->description }}"
                                        style="min-width:60px; background:var(--obe-ink); color:#fff;">
                                        {{ $cpl->code }}
                                    </th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentSemester = null; @endphp
                        @foreach($courses as $course)
                            {{-- Separator semester --}}
                            @if($course->semester !== $currentSemester)
                                @php $currentSemester = $course->semester; @endphp
                                <tr style="background:var(--obe-red-soft);">
                                    <td colspan="{{ $cpls->count() + 2 }}"
                                        class="fw-bold text-uppercase small"
                                        style="color:var(--obe-red); letter-spacing:.05em;">
                                        Semester {{ $currentSemester }}
                                    </td>
                                </tr>
                            @endif

                            @php
                                $cpmkList = $course->cpmks;
                                $rowSpan  = max($cpmkList->count(), 1);
                            @endphp

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
                                            <td rowspan="{{ $rowSpan }}"
                                                style="position:sticky; left:0; background:#fff; vertical-align:top;">
                                                <div class="fw-bold">{{ $course->code }}</div>
                                                <div class="text-muted small">{{ $course->name }}</div>
                                            </td>
                                        @endif
                                        <td>
                                            <span class="badge bg-light text-dark border"
                                                  style="font-family:monospace;">{{ $cpmk->code }}</span>
                                            @if($cpmk->description)
                                                <div class="text-muted small mt-1"
                                                     style="max-width:180px; white-space:normal; line-height:1.3;">
                                                    {{ Str::limit($cpmk->description, 60) }}
                                                </div>
                                            @endif
                                        </td>
                                        @foreach($cpls as $cpl)
                                            @php $supported = $cpmk->cpl_id == $cpl->id; @endphp
                                            <td class="text-center"
                                                style="{{ $supported ? 'background:var(--obe-red-soft);' : '' }}">
                                                @if($supported)
                                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white"
                                                          style="width:24px; height:24px; background:var(--obe-red);"
                                                          title="CPMK {{ $cpmk->code }} mendukung {{ $cpl->code }}">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                             stroke="currentColor" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="text-muted">–</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-sidebar-layout>