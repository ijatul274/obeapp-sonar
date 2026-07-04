<x-sidebar-layout :title="'Daftar Mata Kuliah'" :header="'Mata Kuliah'">

    @if($errors->any() || old('_form_mode'))
        <div class="alert alert-danger small">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 mb-3">
        <form method="GET" action="{{ route('courses.index') }}" class="flex-grow-0" style="min-width:220px;">
            <select name="semester" onchange="this.form.submit()" class="form-select form-select-sm">
                <option value="">Semua Semester</option>
                @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                @endfor
            </select>
        </form>
        <a href="{{ route('courses.create') }}" class="btn btn-obe-red btn-sm ms-sm-auto d-inline-flex align-items-center gap-2">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Mata Kuliah
        </a>
    </div>

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="5"
                   data-filter-cols="3:Sifat"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th style="width:130px;">Kode</th>
                        <th>Mata Kuliah</th>
                        <th class="text-center" style="width:60px;">SKS</th>
                        <th class="text-center" style="width:60px;">W/P</th>
                        <th>CPL Didukung</th>
                        <th class="text-center" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        @php
                            $coursePayload = [
                                'id' => $course->id,
                                'code' => $course->code,
                                'name' => $course->name,
                                'sks' => $course->sks,
                                'semester' => $course->semester,
                                'wajib_pilihan' => $course->wajib_pilihan ?? 'W',
                                'prerequisite_course_id' => $course->prerequisite_course_id,
                            ];
                        @endphp
                        <tr>
                            <td class="fw-semibold" style="font-family:monospace;">{{ $course->code }}</td>
                            <td>{{ $course->name }}</td>
                            <td class="text-center">{{ $course->sks }}</td>
                            <td class="text-center">
                                @php $wp = $course->wajib_pilihan ?? 'W'; @endphp
                                <span class="badge {{ $wp === 'W' ? 'bg-info-subtle text-info' : 'bg-success-subtle text-success' }}" style="font-weight:600;">
                                    {{ $wp }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $cplCodes = $course->cpmks->pluck('cpl')->filter()->unique('id')->sortBy('code')
                                        ->pluck('code')->map(fn($c) => preg_replace('/^CPL[-\s]?/i', '', $c))->toArray();
                                @endphp
                                @if(!empty($cplCodes))
                                    <span class="fw-semibold" style="color:var(--obe-red);">CPL {{ implode(', ', $cplCodes) }}</span>
                                @else
                                    <span class="text-muted fst-italic">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-obe-outline" title="Edit">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('courses.destroy', $course) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Hapus mata kuliah {{ $course->code }} — {{ $course->name }}? Data CPMK terkait juga akan terhapus.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5"><em>Belum ada data mata kuliah.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modal: Tambah / Edit Mata Kuliah ───────────────────────── --}}
    <div class="modal fade" id="courseFormModal" tabindex="-1" aria-labelledby="courseFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="courseForm" method="POST" action="{{ route('courses.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="courseFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="courseFormModalLabel">Tambah Mata Kuliah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height:calc(100vh - 220px); overflow-y:auto;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kode MK <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="cf_code" required
                                       class="form-control" style="font-family:monospace;" placeholder="Contoh: TIF101" autocomplete="off">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Mata Kuliah <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="cf_name" required
                                       class="form-control" placeholder="Contoh: Algoritma dan Pemrograman" autocomplete="off">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">SKS <span class="text-danger">*</span></label>
                                <input type="number" name="sks" id="cf_sks" min="1" required class="form-control" placeholder="3">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                <select name="semester" id="cf_semester" required class="form-select">
                                    <option value="" disabled selected>Pilih Semester</option>
                                    @for($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}">Semester {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Sifat <span class="text-danger">*</span></label>
                                <select name="wajib_pilihan" id="cf_wp" required class="form-select">
                                    <option value="W">Wajib (W)</option>
                                    <option value="P">Pilihan (P)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Mata Kuliah Prasyarat <small class="text-muted fw-normal">(opsional)</small></label>
                                <select name="prerequisite_course_id" id="cf_prereq" class="form-select">
                                    <option value="">Tidak Ada</option>
                                    @foreach($allCourses as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-light border d-flex align-items-start gap-2 mt-3 mb-0 small">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-1" style="color:var(--obe-red);"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>CPMK & indikator dikelola dari halaman <strong>Detail Mata Kuliah</strong> setelah MK disimpan.</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red" id="cf_submitBtn">Simpan Mata Kuliah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function prepareCourseForm(mode, btn) {
        const form  = document.getElementById('courseForm');
        const title = document.getElementById('courseFormModalLabel');
        const submitBtn = document.getElementById('cf_submitBtn');
        const methodInput = document.getElementById('courseFormMethod');
        const baseStore = "{{ route('courses.store') }}";

        // reset
        form.reset();
        document.getElementById('cf_prereq').value = '';

        if (mode === 'create') {
            title.textContent = 'Tambah Mata Kuliah';
            submitBtn.textContent = 'Simpan Mata Kuliah';
            form.action = baseStore;
            methodInput.value = 'POST';
        } else {
            const data = JSON.parse(btn.getAttribute('data-course'));
            title.textContent = 'Edit Mata Kuliah';
            submitBtn.textContent = 'Perbarui Mata Kuliah';
            form.action = "{{ url('courses') }}/" + data.id;
            methodInput.value = 'PUT';
            document.getElementById('cf_code').value     = data.code ?? '';
            document.getElementById('cf_name').value     = data.name ?? '';
            document.getElementById('cf_sks').value      = data.sks ?? '';
            document.getElementById('cf_semester').value = data.semester ?? '';
            document.getElementById('cf_wp').value       = data.wajib_pilihan ?? 'W';
            document.getElementById('cf_prereq').value   = data.prerequisite_course_id ?? '';
        }
    }

    @if($errors->any() && old('_form_mode'))
        document.addEventListener('DOMContentLoaded', () => {
            const m = new bootstrap.Modal(document.getElementById('courseFormModal'));
            m.show();
        });
    @endif
    </script>
</x-sidebar-layout>
