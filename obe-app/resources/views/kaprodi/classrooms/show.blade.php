<x-sidebar-layout :title="'Detail Kelas'" :header="'Detail Kelas: ' . $classroom->name">

    <div class="d-flex justify-content-between align-items-center mb-3">
        @if($classroom->period_type)
            <span class="badge bg-light text-dark border text-uppercase" style="letter-spacing:.04em;">
                {{ $classroom->period_type }} · {{ $classroom->academic_year }}
            </span>
        @endif
        <div class="d-flex gap-2">
            @if(!$classroom->is_archived)
                <a href="{{ route('classrooms.edit', $classroom) }}" class="btn btn-sm btn-obe-red d-inline-flex align-items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Kelas
                </a>
            @endif
            <a href="{{ route('classrooms.index') }}" class="btn btn-sm btn-obe-outline">&larr; Kembali</a>
        </div>
    </div>

    <div class="obe-card mb-3">
        <h2 class="obe-card__title mb-3">Informasi Kelas</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Nama Kelas</div>
                <div class="fw-semibold">{{ $classroom->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Periode Semester</div>
                <div class="fw-semibold">{{ ucfirst($classroom->period_type ?? '-') }} — {{ $classroom->academic_year ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Mata Kuliah</div>
                <div class="fw-semibold">
                    @if($classroom->course)
                        Sem {{ $classroom->course->semester }} · {{ $classroom->course->code }} — {{ $classroom->course->name }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Dosen Pengampu</div>
                <div class="fw-semibold">
                    @if($classroom->lecturer)
                        {{ $classroom->lecturer->name }}{{ $classroom->lecturer->identity ? ' (' . $classroom->lecturer->identity . ')' : '' }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="col-12">
                <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Kode Enrollment Mahasiswa</div>
                <div class="d-flex align-items-center gap-2">
                    <code class="px-3 py-2 rounded fw-bold" style="background:var(--obe-bg); color:var(--obe-red); font-size:1.1rem; letter-spacing:.1em;">{{ $classroom->enrollment_code }}</code>
                    <button type="button" class="btn btn-sm btn-obe-outline"
                            onclick="navigator.clipboard.writeText('{{ $classroom->enrollment_code }}'); this.innerText='✓ Tersalin'; setTimeout(()=>this.innerText='Salin',2000);">Salin</button>
                </div>
            </div>
        </div>
    </div>

    @if($classroom->course)
        <div class="obe-card mb-3">
            <h2 class="obe-card__title mb-3">Detail Mata Kuliah</h2>
            <div class="row g-3 small">
                <div class="col-6 col-md-3">
                    <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Kode</div>
                    <div class="fw-bold" style="font-family:monospace;">{{ $classroom->course->code }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Nama</div>
                    <div class="fw-semibold">{{ $classroom->course->name }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">SKS</div>
                    <div class="fw-semibold">{{ $classroom->course->sks }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size:.7rem;">Semester</div>
                    <div class="fw-semibold">{{ $classroom->course->semester }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="obe-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="obe-card__title mb-0">Mahasiswa Terdaftar</h2>
            <span class="badge bg-light text-dark border">{{ $classroom->students->count() }} orang</span>
        </div>

        @if($classroom->students->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classroom->students as $idx => $student)
                            <tr>
                                <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                <td class="fw-semibold" style="font-family:monospace;">{{ $student->identity }}</td>
                                <td>{{ $student->name }}</td>
                                <td class="text-muted small">{{ $student->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <p class="mb-1 fst-italic">Belum ada mahasiswa terdaftar.</p>
                <small>Bagikan kode <code class="fw-bold" style="color:var(--obe-red);">{{ $classroom->enrollment_code }}</code> ke mahasiswa.</small>
            </div>
        @endif
    </div>

</x-sidebar-layout>
