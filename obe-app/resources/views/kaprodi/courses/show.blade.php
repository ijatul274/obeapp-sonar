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
                    <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">CPL Didukung</div>
                    @if($course->cpls->count() > 0)
                        <div class="border rounded p-2" style="background:var(--obe-bg); max-height:160px; overflow-y:auto;">
                            <ul class="list-unstyled mb-0 small">
                                @foreach($course->cpls as $cpl)
                                    <li class="d-flex gap-2 mb-1">
                                        <span class="fw-bold" style="color:var(--obe-red);">{{ $cpl->code }}</span>
                                        <span>{{ Str::limit($cpl->description, 100) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <span class="text-muted fst-italic small">- Belum ada CPL (terisi otomatis dari CPMK) -</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2 pt-3 border-top mt-3">
                <button type="submit" class="btn btn-obe-red btn-sm">Perbarui Informasi MK</button>
                <a href="{{ route('courses.index') }}" class="btn btn-obe-outline btn-sm">Batal</a>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h6 fw-bold mb-0" style="border-left:3px solid var(--obe-red); padding-left:.6rem;">Daftar CPMK & Indikator <span class="text-muted fw-normal">(Total Bobot: {{ floatval($course->cpmks->sum('percentage')) }}%)</span></h2>
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
                'indicators' => $cpmk->indicators->map(fn($i) => [
                    'description' => $i->description,
                    'percentage'  => (float) $i->percentage,
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
                          onsubmit="return confirm('Hapus CPMK {{ $cpmk->code }}? Indikator terkait juga akan terhapus.');">
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
                        <div class="text-muted small text-uppercase fw-semibold mb-2" style="letter-spacing:.05em;">Indikator</div>
                        @if($cpmk->indicators->count() > 0)
                            <ul class="list-unstyled mb-0">
                                @foreach($cpmk->indicators as $ind)
                                    <li class="d-flex justify-content-between align-items-start gap-2 mb-2 small p-2 border rounded" style="background:var(--obe-bg);">
                                        <span class="d-flex gap-2">
                                            <span style="color:var(--obe-red);">•</span>
                                            <span>{{ $ind->description }}</span>
                                        </span>
                                        <span class="badge bg-light text-dark border">{{ number_format($ind->percentage, 2) }}%</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted small fst-italic mb-0 p-2 border border-dashed rounded">Belum ada indikator.</p>
                        @endif
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
                        <p class="text-muted small mb-3">Isi kode, CPL, pernyataan, dan indikator kinerja CPMK.</p>

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
                                    <input type="number" step="0.01" min="0" max="100" name="percentage" id="cpf_pct" required class="form-control" placeholder="20">
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0 text-uppercase" style="letter-spacing:.05em; font-size:.78rem; border-left:3px solid #16a34a; padding-left:.5rem;">Daftar Indikator Kinerja <span class="text-danger">*</span></h6>
                                <span class="badge" id="cpf_total" style="background:#dcfce7; color:#166534;">Total: 0%</span>
                            </div>
                            <table class="table table-sm align-middle mb-2">
                                <thead style="background:#f8fafc;">
                                    <tr style="font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#64748b;">
                                        <th style="width:40px;">#</th>
                                        <th>Deskripsi Indikator</th>
                                        <th class="text-end" style="width:110px;">Bobot (%)</th>
                                        <th class="text-center" style="width:60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cpf_indList"></tbody>
                                <tfoot>
                                    <tr style="background:#f0fdf4;">
                                        <td class="text-success fw-bold">+</td>
                                        <td><input type="text" id="cpf_newDesc" class="form-control form-control-sm" placeholder="Deskripsi indikator baru..." onkeydown="if(event.key==='Enter'){event.preventDefault();addIndicatorRow();}"></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" min="0" max="100" id="cpf_newPct" class="form-control" placeholder="auto">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-success" onclick="addIndicatorRow()" title="Tambah">+</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <small class="text-muted">Ketik pada baris hijau lalu klik <strong>+</strong> untuk menambah. Kolom <strong>Bobot</strong> dikosongkan = otomatis (dibagi rata), diisi angka = manual.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <span class="small text-muted"><span id="cpf_indCount">0</span> indikator ditambahkan <span id="cpf_indStatus" class="badge bg-light text-dark border ms-1"></span></span>
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

        let cpfIndicators = []; // {description, percentage|null}

        function recalcIndicators() {
            const tbody = document.getElementById('cpf_indList');
            const totalEl = document.getElementById('cpf_total');
            const countEl = document.getElementById('cpf_indCount');
            const statusEl = document.getElementById('cpf_indStatus');

            const manualSum = cpfIndicators.filter(i => i.percentage !== null).reduce((s, i) => s + Number(i.percentage), 0);
            const autoCount = cpfIndicators.filter(i => i.percentage === null).length;
            const autoEach  = autoCount > 0 ? Math.max(0, (100 - manualSum) / autoCount) : 0;
            const total     = manualSum + (autoEach * autoCount);

            tbody.innerHTML = cpfIndicators.map((ind, idx) => {
                const pct = ind.percentage === null ? autoEach : Number(ind.percentage);
                const isAuto = ind.percentage === null;
                return `
                <tr>
                    <td class="text-muted small">${idx + 1}</td>
                    <td>${ind.description}</td>
                    <td class="text-end fw-bold" style="color:#7c3aed;">${pct.toFixed(2)}%${isAuto ? ' <small class="text-muted">(auto)</small>' : ''}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeIndicatorRow(${idx})" title="Hapus">🗑</button>
                    </td>
                </tr>`;
            }).join('');

            // Hidden inputs for submit
            // Append into a managed area at bottom of body
            let hidden = document.getElementById('cpf_hiddenInputs');
            if (!hidden) {
                hidden = document.createElement('div');
                hidden.id = 'cpf_hiddenInputs';
                hidden.style.display = 'none';
                document.getElementById('cpmkForm').appendChild(hidden);
            }
            hidden.innerHTML = '';
            cpfIndicators.forEach(ind => {
                const dInp = document.createElement('input');
                dInp.type = 'hidden'; dInp.name = 'indicator_descriptions[]'; dInp.value = ind.description;
                hidden.appendChild(dInp);
                const pInp = document.createElement('input');
                pInp.type = 'hidden'; pInp.name = 'indicator_percentages[]';
                pInp.value = ind.percentage === null ? '' : ind.percentage;
                hidden.appendChild(pInp);
            });
            const hasManual = cpfIndicators.some(i => i.percentage !== null);
            document.getElementById('cpf_iwt').value = hasManual ? 'manual' : 'otomatis';

            totalEl.textContent = 'Total: ' + total.toFixed(1) + '%';
            const ok = Math.abs(total - 100) < 0.01 || cpfIndicators.length === 0;
            totalEl.style.background = ok ? '#dcfce7' : '#fee2e2';
            totalEl.style.color      = ok ? '#166534' : '#b91c1c';
            countEl.textContent = cpfIndicators.length;
            statusEl.textContent = (cpfIndicators.length > 0 && ok) ? 'Siap disimpan' : '';
        }

        function addIndicatorRow() {
            const desc = document.getElementById('cpf_newDesc').value.trim();
            const pctRaw = document.getElementById('cpf_newPct').value.trim();
            if (!desc) return;
            cpfIndicators.push({ description: desc, percentage: pctRaw === '' ? null : Number(pctRaw) });
            document.getElementById('cpf_newDesc').value = '';
            document.getElementById('cpf_newPct').value = '';
            recalcIndicators();
        }

        function removeIndicatorRow(idx) {
            cpfIndicators.splice(idx, 1);
            recalcIndicators();
        }

        function prepareCpmkForm(mode, btn) {
            const form  = document.getElementById('cpmkForm');
            const title = document.getElementById('cpmkFormModalLabel');
            const submit = document.getElementById('cpf_submit');
            const methodInput = document.getElementById('cpmkFormMethod');

            form.reset();
            cpfIndicators = [];

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
                document.getElementById('cpf_cpl').value  = data.cpl_id ?? '';
                document.getElementById('cpf_lect').value = data.lecturer_id ?? '';
                document.getElementById('cpf_pct').value  = data.percentage ?? '';
                document.getElementById('cpf_desc').value = data.description ?? '';
                cpfIndicators = (data.indicators || []).map(i => ({
                    description: i.description,
                    percentage: i.percentage,
                }));
            }
            recalcIndicators();
        }
    </script>
</x-sidebar-layout>
