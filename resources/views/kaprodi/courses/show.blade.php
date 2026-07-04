<x-sidebar-layout :title="'Edit Mata Kuliah'" :header="'Edit Mata Kuliah & CPMK'">

    @if($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0">Informasi Mata Kuliah</h2>
        <a href="{{ route('courses.index') }}" class="text-decoration-none small text-muted">&larr; Kembali ke Daftar MK</a>
    </div>

    <div class="obe-card mb-4">
        <form method="POST" action="{{ route('courses.update', $course) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                    <input type="text" name="code" required value="{{ old('code', $course->code) }}"
                           class="form-control @error('code') is-invalid @enderror" style="font-family:monospace;" autocomplete="off">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">Nama Mata Kuliah <span class="text-danger">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $course->name) }}"
                           class="form-control @error('name') is-invalid @enderror" autocomplete="off">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">SKS <span class="text-danger">*</span></label>
                    <input type="number" name="sks" min="1" required value="{{ old('sks', $course->sks) }}"
                           class="form-control @error('sks') is-invalid @enderror">
                    @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                    <select name="semester" required class="form-select @error('semester') is-invalid @enderror">
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ old('semester', $course->semester) == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                    @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Sifat <span class="text-danger">*</span></label>
                    <select name="wajib_pilihan" required class="form-select @error('wajib_pilihan') is-invalid @enderror">
                        <option value="W" {{ old('wajib_pilihan', $course->wajib_pilihan ?? 'W') === 'W' ? 'selected' : '' }}>Wajib (W)</option>
                        <option value="P" {{ old('wajib_pilihan', $course->wajib_pilihan ?? 'W') === 'P' ? 'selected' : '' }}>Pilihan (P)</option>
                    </select>
                    @error('wajib_pilihan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">MK Prasyarat</label>
                    <select name="prerequisite_course_id" class="form-select @error('prerequisite_course_id') is-invalid @enderror">
                        <option value="">— Tidak ada —</option>
                        @foreach($prereqCourses as $prereq)
                            <option value="{{ $prereq->id }}" {{ old('prerequisite_course_id', $course->prerequisite_course_id) == $prereq->id ? 'selected' : '' }}>
                                {{ $prereq->code }} — {{ $prereq->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('prerequisite_course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    @php $boundCplIds = $course->cpls->pluck('id')->all(); @endphp
                    <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">CPL Didukung <span class="text-danger">*</span></div>
                    @if($allCpls->isEmpty())
                        <span class="text-muted fst-italic small">- Belum ada CPL pada program studi ini. Tambahkan CPL terlebih dahulu di menu CPL. -</span>
                    @else
                        <div class="border rounded p-2 @error('cpl_ids') border-danger @enderror" style="background:var(--obe-bg); max-height:200px; overflow-y:auto;">
                            @foreach($allCpls as $cpl)
                                <label class="d-flex gap-2 align-items-start mb-2 small" style="cursor:pointer;">
                                    <input type="checkbox" name="cpl_ids[]" value="{{ $cpl->id }}" class="form-check-input mt-1"
                                           {{ in_array($cpl->id, old('cpl_ids', $boundCplIds)) ? 'checked' : '' }}>
                                    <span>
                                        <span class="fw-bold" style="color:var(--obe-red);">{{ $cpl->code }}</span>
                                        <span>{{ Str::limit($cpl->description, 100) }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-muted">Centang CPL yang dibebankan ke mata kuliah ini. CPL yang dicentang menjadi pilihan saat menambah/mengedit CPMK.</small>
                    @endif
                    @error('cpl_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex gap-2 pt-3 border-top mt-3">
                <button type="submit" class="btn btn-obe-red btn-sm">Perbarui Informasi MK</button>
                <a href="{{ route('courses.index') }}" class="btn btn-obe-outline btn-sm">Batal</a>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0" style="border-left:3px solid var(--obe-red); padding-left:.6rem;">Daftar CPMK & Sub-CPMK <span class="text-muted fw-normal">(Total Bobot: {{ floatval($course->cpmks->sum('percentage')) }}%)</span></h2>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#cpmkFormModal"
                onclick="prepareCpmkForm('create')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah CPMK
        </button>
    </div>

    @forelse($course->cpmks as $cpmk)
        @php
            $cpmkPayload = [
                'id' => $cpmk->id,
                'code' => $cpmk->code,
                'cpl_id' => (string) ($cpmk->cpl_id ?? ''),
                'lecturer_id' => (string) ($cpmk->lecturer_id ?? ''),
                'percentage' => $cpmk->percentage,
                'description' => $cpmk->description,
                'subcpmks' => $cpmk->subCpmks->map(fn($s) => [
                    'description' => $s->description,
                    'percentage'  => (float) $s->percentage,
                    'meetings'    => $s->meetings,
                    'indicators'  => $s->indicators->map(fn($i) => [
                        'description' => $i->description,
                        'percentage'  => (float) $i->percentage,
                    ])->values()->all(),
                ])->values()->all(),
            ];
        @endphp
        <div class="obe-card mb-3 p-0 overflow-hidden">
            <div class="px-3 py-3 d-flex flex-wrap align-items-start gap-2 border-bottom" style="background:var(--obe-bg);">
                <span class="badge" style="background:var(--obe-red); color:#fff;">{{ $cpmk->code }} ({{ floatval($cpmk->percentage) }}%)</span>
                <span class="badge bg-light text-dark border">{{ $cpmk->meeting_range }}</span>
                <p class="mb-0 fw-semibold flex-grow-1 ms-2">{{ $cpmk->description }}</p>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-obe-outline" title="Edit CPMK"
                            data-bs-toggle="modal" data-bs-target="#cpmkFormModal"
                            data-cpmk="{{ json_encode($cpmkPayload) }}"
                            onclick="prepareCpmkForm('edit', this)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <form action="{{ route('cpmks.destroy', $cpmk) }}" method="POST" class="m-0"
                          onsubmit="return confirm('Hapus CPMK {{ $cpmk->code }}? Sub-CPMK terkait juga akan terhapus.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            <div class="p-3">
                <div class="row g-3">
                    @if($cpmk->cpl)
                    <div class="col-md-4 col-lg-3 border-end">
                        <div class="text-muted small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;">CPL Didukung</div>
                        <span class="badge bg-light text-dark border mb-2">{{ $cpmk->cpl->code }}</span>
                        <p class="text-muted small mb-0">{{ Str::limit($cpmk->cpl->description, 100) }}</p>
                    </div>
                    @endif
                    <div class="col-md">
                        <div class="text-muted small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;">Sub-CPMK &amp; Indikator</div>
                        @forelse($cpmk->subCpmks as $sub)
                            <div class="mb-2 border rounded" style="background:var(--obe-bg);">
                                <div class="d-flex justify-content-between align-items-center gap-2 px-2 py-1 border-bottom">
                                    <span class="small fw-semibold">{{ $sub->description }}</span>
                                    <span class="d-flex align-items-center gap-1">
                                        @if($sub->meetings)<span class="badge bg-light text-dark border">{{ $sub->meetings }}× pertemuan</span>@endif
                                        <span class="badge bg-success">{{ number_format($sub->percentage, 2) }}%</span>
                                    </span>
                                </div>
                                @if($sub->indicators->count() > 0)
                                    <ul class="list-unstyled mb-0 py-1">
                                        @foreach($sub->indicators as $ind)
                                            <li class="d-flex justify-content-between align-items-start gap-2 small px-2 py-1">
                                                <span class="d-flex gap-2"><span style="color:#7c3aed;">▸</span><span>{{ $ind->description }}</span></span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted small fst-italic mb-0 px-2 py-1">Belum ada indikator.</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small fst-italic mb-0 p-2 border border-dashed rounded">Belum ada Sub-CPMK.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5 border border-dashed rounded">
            <p class="text-muted mb-3">Belum ada CPMK untuk mata kuliah ini.</p>
            <button type="button" class="btn btn-obe-red btn-sm"
                    data-bs-toggle="modal" data-bs-target="#cpmkFormModal"
                    onclick="prepareCpmkForm('create')">+ Tambah CPMK</button>
        </div>
    @endforelse

    {{-- ── Modal: Tambah / Edit CPMK ───────────────────────────── --}}
    <div class="modal fade" id="cpmkFormModal" tabindex="-1" aria-labelledby="cpmkFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="cpmkForm" method="POST" action="{{ route('cpmks.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="cpmkFormMethod" value="POST">
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <input type="hidden" name="indicator_weight_type" id="cpf_iwt" value="manual">

                    <div class="modal-header">
                        <h5 class="modal-title" id="cpmkFormModalLabel">Tambah CPMK Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height:calc(100vh - 220px); overflow-y:auto;">
                        <p class="text-muted small mb-3">Isi kode, CPL, pernyataan, dan Sub-CPMK dari CPMK.</p>

                        <div class="border rounded p-3 mb-3">
                            <h6 class="fw-bold mb-3 text-uppercase" style="letter-spacing:.05em; font-size:.78rem; border-left:3px solid #16a34a; padding-left:.5rem;">Data CPMK</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Kode CPMK <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="cpf_code" required class="form-control" placeholder="Contoh: CPMK-01">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">CPL yang Didukung <span class="text-danger">*</span></label>
                                    <select name="cpl_id" id="cpf_cpl" required class="form-select">
                                        <option value="">-- Pilih CPL --</option>
                                        @foreach($cpls as $cpl)
                                            <option value="{{ $cpl->id }}">{{ $cpl->code }} — {{ Str::limit($cpl->description, 60) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Bobot CPMK (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="1" max="100" name="percentage" id="cpf_pct" required class="form-control" placeholder="20"
                                           oninput="if(this.value>100)this.value=100;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Pernyataan CPMK <span class="text-danger">*</span></label>
                                    <textarea name="description" id="cpf_desc" rows="3" required class="form-control" placeholder="Tuliskan pernyataan capaian pembelajaran mata kuliah..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Dosen Pengampu <small class="text-muted fw-normal">(opsional)</small></label>
                                    <select name="lecturer_id" id="cpf_lect" class="form-select">
                                        <option value="">— Tidak ada dosen spesifik —</option>
                                        @foreach($lecturers as $l)
                                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0 text-uppercase" style="letter-spacing:.05em; font-size:.78rem; border-left:3px solid #16a34a; padding-left:.5rem;">Sub-CPMK &amp; Indikator</h6>
                                <span class="badge" id="cpf_subTotal" style="background:#dcfce7; color:#166534;">Total Sub: 0%</span>
                            </div>
                            <p class="text-muted small mb-2">Tiap CPMK terdiri dari beberapa <strong>Sub-CPMK</strong>; tiap Sub-CPMK punya beberapa <strong>Indikator</strong> (alat ukur). <strong>Bobot Sub-CPMK dihitung otomatis dari jumlah pertemuannya</strong> (proporsional). Anda cukup menentukan <strong>indikator apa saja</strong> yang dinilai — <strong>bobot tiap indikator ditentukan oleh dosen pengampu</strong> saat mengajar.</p>

                            <div id="cpf_subList" class="d-flex flex-column gap-2 mb-2"></div>

                            <div class="d-flex gap-2 align-items-end border-top pt-2">
                                <div class="flex-grow-1">
                                    <label class="form-label small mb-1 fw-semibold">Tambah Sub-CPMK</label>
                                    <input type="text" id="cpf_newSubDesc" class="form-control form-control-sm" placeholder="Deskripsi Sub-CPMK..." onkeydown="if(event.key==='Enter'){event.preventDefault();addSubRow();}">
                                </div>
                                <div style="width:130px;">
                                    <label class="form-label small mb-1 fw-semibold">Pertemuan <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" min="1" max="16" id="cpf_newSubMeet" class="form-control" placeholder="1" title="Jumlah pertemuan untuk Sub-CPMK ini — menentukan bobotnya">
                                        <span class="input-group-text">&times;</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-success" onclick="addSubRow()">+ Sub-CPMK</button>
                            </div>
                            <div id="cpf_hiddenInputs" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <span class="small text-muted">Bobot Sub-CPMK dihitung dari pertemuan; bobot Indikator diatur dosen pengampu.</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-obe-red" id="cpf_submit">✓ Tambahkan CPMK</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const ROUTE_CPMK_STORE = "{{ route('cpmks.store') }}";
        const URL_CPMK_BASE    = "{{ url('cpmks') }}";

        // State: [{description, percentage|null, indicators:[{description, percentage|null}]}]
        let cpfSubs = [];

        function cpfEsc(s) {
            return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        // Bagi 100% ke nilai: manual dipakai apa adanya, null dibagi rata sisa.
        function cpfResolve(pcts) {
            const manual = pcts.filter(p => p !== null).reduce((s, p) => s + Number(p), 0);
            const autoN = pcts.filter(p => p === null).length;
            const autoEach = autoN > 0 ? Math.max(0, (100 - manual) / autoN) : 0;
            return { display: pcts.map(p => p === null ? autoEach : Number(p)), total: manual + autoEach * autoN };
        }

        // Bobot Sub-CPMK proporsional dari jumlah pertemuan (kosong dianggap 1).
        function cpfResolveMeetings(meets) {
            const counts = meets.map(m => (m === null || m === undefined || Number(m) < 1) ? 1 : Number(m));
            const total = counts.reduce((s, c) => s + c, 0);
            if (total <= 0) return { display: counts.map(() => 0), total: 0 };
            return { display: counts.map(c => c / total * 100), total: 100 };
        }

        function renderSubs() {
            const list = document.getElementById('cpf_subList');
            const subRes = cpfResolveMeetings(cpfSubs.map(s => s.meetings));

            list.innerHTML = cpfSubs.map((sub, si) => {
                const indRows = sub.indicators.map((ind, ii) => `
                    <li class="d-flex justify-content-between align-items-center gap-2 small py-1 ps-3 pe-2">
                        <span><span style="color:#7c3aed;">&#9656;</span> ${cpfEsc(ind.description)}</span>
                        <span class="d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0" onclick="removeInd(${si},${ii})" title="Hapus indikator">&#128465;</button>
                        </span>
                    </li>`).join('') || '<li class="text-muted small fst-italic ps-3 py-1">Belum ada indikator.</li>';

                return `
                <div class="border rounded" style="background:#f8fafc;">
                    <div class="d-flex justify-content-between align-items-center gap-2 px-2 py-2 border-bottom">
                        <span class="fw-semibold small"><span class="badge bg-success me-1">Sub ${si + 1}</span>${cpfEsc(sub.description)}</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border">${sub.meetings ? sub.meetings : 1}&times; pertemuan</span>
                            <span class="badge" style="background:#dcfce7; color:#166534;" title="Bobot otomatis dari pertemuan">${subRes.display[si].toFixed(2)}%</span>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0" onclick="removeSub(${si})" title="Hapus Sub-CPMK">&#128465;</button>
                        </span>
                    </div>
                    <ul class="list-unstyled mb-1 mt-1">${indRows}</ul>
                    <div class="d-flex gap-2 align-items-center px-2 pb-2">
                        <input type="text" class="form-control form-control-sm" id="cpf_newIndDesc_${si}" placeholder="Indikator baru (alat ukur)..." onkeydown="if(event.key==='Enter'){event.preventDefault();addInd(${si});}">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addInd(${si})" title="Tambah indikator">+ Indikator</button>
                    </div>
                </div>`;
            }).join('') || '<p class="text-muted small fst-italic mb-0">Belum ada Sub-CPMK. Tambahkan di bawah.</p>';

            const totalEl = document.getElementById('cpf_subTotal');
            const ok = Math.abs(subRes.total - 100) < 0.01 || cpfSubs.length === 0;
            totalEl.textContent = 'Total Sub: ' + subRes.total.toFixed(1) + '%';
            totalEl.style.background = ok ? '#dcfce7' : '#fee2e2';
            totalEl.style.color = ok ? '#166534' : '#b91c1c';

            let html = '';
            cpfSubs.forEach((sub, si) => {
                html += `<input type="hidden" name="subcpmks[${si}][description]" value="${cpfEsc(sub.description)}">`;
                html += `<input type="hidden" name="subcpmks[${si}][percentage]" value="${sub.percentage === null ? '' : sub.percentage}">`;
                html += `<input type="hidden" name="subcpmks[${si}][meetings]" value="${sub.meetings === null || sub.meetings === undefined ? '' : sub.meetings}">`;
                sub.indicators.forEach((ind, ii) => {
                    html += `<input type="hidden" name="subcpmks[${si}][indicators][${ii}][description]" value="${cpfEsc(ind.description)}">`;
                    html += `<input type="hidden" name="subcpmks[${si}][indicators][${ii}][percentage]" value="${ind.percentage === null ? '' : ind.percentage}">`;
                });
            });
            document.getElementById('cpf_hiddenInputs').innerHTML = html;
        }

        function addSubRow() {
            const d = document.getElementById('cpf_newSubDesc');
            const m = document.getElementById('cpf_newSubMeet');
            const desc = d.value.trim();
            if (!desc) return;
            const meet = m.value.trim();
            cpfSubs.push({ description: desc, percentage: null, meetings: meet === '' ? null : Number(meet), indicators: [] });
            d.value = ''; m.value = '';
            renderSubs();
        }

        function removeSub(si) { cpfSubs.splice(si, 1); renderSubs(); }

        function addInd(si) {
            const d = document.getElementById('cpf_newIndDesc_' + si);
            const desc = d.value.trim();
            if (!desc) return;
            cpfSubs[si].indicators.push({ description: desc, percentage: null });
            renderSubs();
        }

        function removeInd(si, ii) { cpfSubs[si].indicators.splice(ii, 1); renderSubs(); }

        function prepareCpmkForm(mode, btn) {
            const form = document.getElementById('cpmkForm');
            const title = document.getElementById('cpmkFormModalLabel');
            const submit = document.getElementById('cpf_submit');
            const methodInput = document.getElementById('cpmkFormMethod');

            form.reset();
            cpfSubs = [];

            if (mode === 'create') {
                title.textContent = 'Tambah CPMK Baru';
                submit.innerHTML = '✓ Tambahkan CPMK';
                form.action = ROUTE_CPMK_STORE;
                methodInput.value = 'POST';
            } else {
                const data = JSON.parse(btn.getAttribute('data-cpmk'));
                title.textContent = 'Edit CPMK';
                submit.innerHTML = '✓ Perbarui CPMK';
                form.action = URL_CPMK_BASE + '/' + data.id;
                methodInput.value = 'PUT';
                document.getElementById('cpf_code').value = data.code ?? '';
                document.getElementById('cpf_cpl').value = data.cpl_id ?? '';
                document.getElementById('cpf_lect').value = data.lecturer_id ?? '';
                document.getElementById('cpf_pct').value = data.percentage ?? '';
                document.getElementById('cpf_desc').value = data.description ?? '';
                cpfSubs = (data.subcpmks || []).map(s => ({
                    description: s.description,
                    percentage: s.percentage,
                    meetings: s.meetings ?? null,
                    indicators: (s.indicators || []).map(i => ({ description: i.description, percentage: i.percentage })),
                }));
            }
            renderSubs();
        }
    </script>
</x-sidebar-layout>