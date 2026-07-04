<x-sidebar-layout :title="'Arsip Kelas'" :header="'Arsip Kelas'">

    <p class="text-muted small mb-3">Daftar kelas yang telah diarsipkan.</p>

    <div class="obe-card mb-3">
        <form method="GET" action="{{ route('kaprodi.arsip.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-uppercase" style="font-size:.7rem;">Tahun Ajaran</label>
                <select name="academic_year" onchange="this.form.submit()" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $filterYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-uppercase" style="font-size:.7rem;">Periode</label>
                <select name="period_type" onchange="this.form.submit()" class="form-select form-select-sm">
                    <option value="">Semua Periode</option>
                    <option value="ganjil" {{ request('period_type') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="genap" {{ request('period_type') === 'genap' ? 'selected' : '' }}>Genap</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-uppercase" style="font-size:.7rem;">Semester MK</label>
                <select name="semester" onchange="this.form.submit()" class="form-select form-select-sm">
                    <option value="">Semua Semester</option>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <a href="{{ route('kaprodi.arsip.index') }}" class="btn btn-obe-outline btn-sm w-100">Reset Filter</a>
            </div>
        </form>
    </div>

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="0,6"
                   data-filter-cols="3:Periode"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th class="text-center" style="width:40px;">No</th>
                        <th>Nama Kelas</th>
                        <th>Mata Kuliah</th>
                        <th class="text-center" style="width:110px;">Periode</th>
                        <th>Dosen Pengampu</th>
                        <th>CPMK</th>
                        <th class="text-center" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classrooms as $idx => $classroom)
                        @php
                            $lecturers = $classroom->cpmkLecturers;
                            $lectCpmks = [];
                            foreach ($classroom->cpmks as $cp) {
                                $lid = $cp->pivot->lecturer_id ?? null;
                                if ($lid) $lectCpmks[$lid][] = $cp->code;
                            }
                        @endphp
                        <tr>
                            <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $classroom->name }}</div>
                                <div class="text-muted small">{{ $classroom->academic_year ?? '' }}</div>
                            </td>
                            <td>
                                @if($classroom->course)
                                    <div class="fw-semibold small">{{ $classroom->course->name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $classroom->course->code }} · Sem {{ $classroom->course->semester }} · {{ $classroom->course->sks }} SKS</div>
                                @else
                                    <span class="text-muted fst-italic">-</span>
                                @endif
                            </td>
                            <td class="text-center small">
                                @if($classroom->period_type)
                                    <div class="fw-semibold">{{ ucfirst($classroom->period_type) }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">{{ $classroom->academic_year ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="small">
                                @if($lecturers->isEmpty())
                                    <span class="text-muted">—</span>
                                @else
                                    @foreach($lecturers as $lect)
                                        <div class="fw-semibold">{{ $lect->initials ?? $lect->identity ?? '-' }}</div>
                                    @endforeach
                                @endif
                            </td>
                            <td class="small">
                                @if($lecturers->isEmpty())
                                    <span class="text-muted">—</span>
                                @else
                                    @foreach($lecturers as $lect)
                                        <div>{{ isset($lectCpmks[$lect->id]) ? implode(', ', $lectCpmks[$lect->id]) : '—' }}</div>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('classrooms.archive', $classroom) }}" method="POST" class="m-0" onsubmit="return confirm('Kembalikan kelas ini dari arsip?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-obe-outline">Pulihkan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><em>Belum ada kelas yang diarsipkan.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-sidebar-layout>
