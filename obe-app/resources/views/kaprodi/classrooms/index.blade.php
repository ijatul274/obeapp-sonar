<x-sidebar-layout :title="'Kelola Kelas'" :header="'Kelola Kelas'">

    @if($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">
            Periode aktif:
            <span class="fw-semibold" style="color:var(--obe-red);">{{ ucfirst($activePeriod['period_type']) }} {{ $activePeriod['academic_year'] }}</span>
        </p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#classroomFormModal"
                onclick="prepareClassroomForm('create')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Kelas
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="obe-card mb-3">
        <form method="GET" action="{{ route('classrooms.index') }}" class="row g-2 align-items-end">
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
                <a href="{{ route('classrooms.index') }}" class="btn btn-obe-outline btn-sm w-100">Reset Filter</a>
            </div>
        </form>
    </div>

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="0,8"
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
                        <th class="text-center" style="width:120px;">Pertemuan</th>
                        <th class="text-center" style="width:130px;">Kode Kelas</th>
                        <th class="text-center" style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classrooms as $idx => $classroom)
                        @php $rowNo = $idx + 1; @endphp
                        @php
                            $lecturers = $classroom->cpmkLecturers;
                            $lectCpmks = [];
                            $lectMeetings = [];
                            $cpmkLecturerMap = [];
                            foreach ($classroom->cpmks as $cp) {
                                $lid = $cp->pivot->lecturer_id ?? null;
                                if ($lid) {
                                    $lectCpmks[$lid][] = $cp->code;
                                    if ($cp->meeting_start && $cp->meeting_end) {
                                        $lectMeetings[$lid][] = (int)$cp->meeting_start . '–' . (int)$cp->meeting_end;
                                    }
                                }
                                $cpmkLecturerMap[$cp->id] = $lid ? (string) $lid : '';
                            }
                            $classroomPayload = [
                                'id' => $classroom->id,
                                'name' => $classroom->name,
                                'course_id' => (string) $classroom->course_id,
                                'academic_year' => $classroom->academic_year,
                                'period_type' => $classroom->period_type,
                                'cpmk_lecturers' => $cpmkLecturerMap,
                            ];
                        @endphp
                        <tr>
                            <td class="text-center text-muted small">{{ $rowNo }}</td>
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
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($lecturers->isEmpty())
                                    <span class="fst-italic" style="color:var(--obe-red);">Belum ditentukan</span>
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
                            <td class="text-center small">
                                @if($lecturers->isEmpty())
                                    <span class="text-muted">—</span>
                                @else
                                    @foreach($lecturers as $lect)
                                        <div>{{ isset($lectMeetings[$lect->id]) ? implode(', ', $lectMeetings[$lect->id]) : '—' }}</div>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border" style="font-family:monospace;">{{ $classroom->enrollment_code }}</span>
                                <button type="button" class="btn btn-sm p-0 ms-1 text-muted border-0 bg-transparent"
                                        title="Salin kode"
                                        onclick="navigator.clipboard.writeText('{{ $classroom->enrollment_code }}'); this.innerText='✓'; setTimeout(()=>this.innerText='⎘', 1500);">⎘</button>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-obe-outline" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#classroomFormModal"
                                            data-classroom="{{ json_encode($classroomPayload) }}"
                                            onclick="prepareClassroomForm('edit', this)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-sm btn-obe-outline" title="Detail / Mahasiswa">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <form action="{{ route('classrooms.archive', $classroom) }}" method="POST" class="m-0" onsubmit="return confirm('Arsipkan kelas ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-obe-outline" title="Arsipkan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('classrooms.destroy', $classroom) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus kelas ini permanen?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5"><em>Belum ada kelas pada filter ini.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- ── Modal: Tambah / Edit Kelas ─────────────────────────────── --}}
    <div class="modal fade" id="classroomFormModal" tabindex="-1" aria-labelledby="classroomFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="classroomForm" method="POST" action="{{ route('classrooms.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="classroomFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center gap-2" id="classroomFormModalLabel">
                            <span style="color:#16a34a; font-size:1.4rem; line-height:1;">+</span>
                            <span id="cf2_title">Tambah Kelas Baru</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height:calc(100vh - 220px); overflow-y:auto;">
                        <p class="text-muted small mb-3">Isi detail kelas dan tentukan dosen pengampu per CPMK.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="cf2_name" required
                                       class="form-control" placeholder="Contoh: TI-A, Reguler 1" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                                <input type="text" name="academic_year" id="cf2_year" required
                                       class="form-control" style="font-family:monospace;" placeholder="2025/2026"
                                       value="{{ $activePeriod['academic_year'] }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                                <select name="period_type" id="cf2_period" required class="form-select">
                                    <option value="ganjil" {{ $activePeriod['period_type'] === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                    <option value="genap" {{ $activePeriod['period_type'] === 'genap' ? 'selected' : '' }}>Genap</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
                                <select name="course_id" id="cf2_course" required class="form-select">
                                    <option value="">— Pilih Mata Kuliah —</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4 mb-2 d-flex align-items-center gap-2" style="border-left:3px solid #16a34a; padding-left:.5rem; text-transform:uppercase; letter-spacing:.04em; font-size:.82rem;">
                            Penugasan Dosen per CPMK
                        </h6>
                        <p class="text-muted small mb-3">Pilih dosen untuk setiap CPMK. Kosongkan jika belum ada penugasan.</p>

                        <div id="cf2_cpmkList">
                            <div class="text-center text-muted small py-4 fst-italic" id="cf2_cpmkPlaceholder">
                                Pilih mata kuliah terlebih dahulu untuk melihat daftar CPMK.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red" id="cf2_submitBtn">✓ Simpan Kelas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $jsCourses = $allCourses->map(fn($c) => [
            'id'   => (string)$c->id,
            'code' => $c->code,
            'name' => $c->name,
            'sem'  => (int) $c->semester,
            'type' => in_array($c->semester, [1,3,5,7]) ? 'ganjil' : 'genap',
            'cpmks'=> $c->cpmks->map(fn($cp) => [
                'id'       => (string)$cp->id,
                'code'     => $cp->code,
                'desc'     => $cp->description,
                'meetings' => $cp->meeting_range,
            ])->values(),
        ])->values();
        $jsDosens = $dosens->map(fn($d) => [
            'id'    => (string)$d->id,
            'name'  => $d->name,
            'ident' => $d->identity ?? '',
        ])->values();
    @endphp

    <script>
        const ALL_COURSES = @json($jsCourses);
        const ALL_DOSENS  = @json($jsDosens);
        const ROUTE_STORE = "{{ route('classrooms.store') }}";
        const URL_BASE    = "{{ url('classrooms') }}";

        let editingCpmkLecturers = {};

        function buildDosenOptions(selectedId = '') {
            let html = '<option value="">— Pilih Dosen —</option>';
            ALL_DOSENS.forEach(d => {
                const sel = String(d.id) === String(selectedId) ? 'selected' : '';
                const id  = d.ident ? ` (${d.ident})` : '';
                html += `<option value="${d.id}" ${sel}>${d.name}${id}</option>`;
            });
            return html;
        }

        function refreshCourseSelect(period, selectedCourseId = '') {
            const sel = document.getElementById('cf2_course');
            sel.innerHTML = '<option value="">— Pilih Mata Kuliah —</option>';
            ALL_COURSES.filter(c => c.type === period).forEach(c => {
                const o = document.createElement('option');
                o.value = c.id;
                o.textContent = `${c.code} — ${c.name} (Sem ${c.sem})`;
                if (String(c.id) === String(selectedCourseId)) o.selected = true;
                sel.appendChild(o);
            });
        }

        function refreshCpmkList(courseId) {
            const wrap = document.getElementById('cf2_cpmkList');
            const course = ALL_COURSES.find(c => String(c.id) === String(courseId));
            if (!course || !course.cpmks.length) {
                wrap.innerHTML = `<div class="text-center text-muted small py-4 fst-italic">${course ? 'Mata kuliah ini belum punya CPMK.' : 'Pilih mata kuliah terlebih dahulu untuk melihat daftar CPMK.'}</div>`;
                return;
            }
            let html = '';
            course.cpmks.forEach(cp => {
                const sel = editingCpmkLecturers[cp.id] || '';
                html += `
                <div class="border rounded p-3 mb-2" style="background:#f8fafc;">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge" style="background:#dcfce7; color:#166534; font-weight:700;">${cp.code}</span>
                        <span class="badge" style="background:#fef3c7; color:#854d0e;">Pertemuan ${cp.meetings || '—'}</span>
                    </div>
                    <label class="form-label small fw-semibold mb-1">${cp.code} — ${(cp.desc || cp.code).slice(0, 80)}</label>
                    <select name="cpmk_lecturers[${cp.id}]" class="form-select form-select-sm">${buildDosenOptions(sel)}</select>
                </div>`;
            });
            wrap.innerHTML = html;
        }

        function prepareClassroomForm(mode, btn) {
            const form  = document.getElementById('classroomForm');
            const title = document.getElementById('cf2_title');
            const submitBtn = document.getElementById('cf2_submitBtn');
            const methodInput = document.getElementById('classroomFormMethod');

            form.reset();
            editingCpmkLecturers = {};

            if (mode === 'create') {
                title.textContent = 'Tambah Kelas Baru';
                submitBtn.innerHTML = '✓ Simpan Kelas';
                form.action = ROUTE_STORE;
                methodInput.value = 'POST';
                document.getElementById('cf2_year').value = "{{ $activePeriod['academic_year'] }}";
                document.getElementById('cf2_period').value = "{{ $activePeriod['period_type'] }}";
                refreshCourseSelect(document.getElementById('cf2_period').value);
                refreshCpmkList(null);
            } else {
                const data = JSON.parse(btn.getAttribute('data-classroom'));
                title.textContent = 'Edit Kelas';
                submitBtn.innerHTML = '✓ Perbarui Kelas';
                form.action = URL_BASE + '/' + data.id;
                methodInput.value = 'PUT';
                document.getElementById('cf2_name').value = data.name ?? '';
                document.getElementById('cf2_year').value = data.academic_year ?? '';
                document.getElementById('cf2_period').value = data.period_type ?? 'ganjil';
                editingCpmkLecturers = data.cpmk_lecturers || {};
                refreshCourseSelect(data.period_type, data.course_id);
                refreshCpmkList(data.course_id);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('cf2_period').addEventListener('change', e => {
                editingCpmkLecturers = {};
                refreshCourseSelect(e.target.value);
                refreshCpmkList(null);
            });
            document.getElementById('cf2_course').addEventListener('change', e => {
                refreshCpmkList(e.target.value);
            });
        });
    </script>
</x-sidebar-layout>
