<x-sidebar-layout :title="'Tambah Mata Kuliah'" :header="'Tambah Mata Kuliah'">

    @if($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <form id="courseCreateForm" method="POST" action="{{ route('courses.store') }}" onsubmit="return validateCourseSubmit(event)">
        @csrf

        <div id="cpmkTotalAlert" class="alert alert-danger small d-none mb-3"></div>

        <div class="obe-card mb-3">
            <h2 class="obe-card__title mb-3" style="border-bottom:2px solid var(--obe-red); padding-bottom:.5rem;">Detail Mata Kuliah</h2>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kode MK <span class="text-danger">*</span></label>
                <input type="text" name="code" required value="{{ old('code') }}"
                       class="form-control @error('code') is-invalid @enderror"
                       style="font-family:monospace;" placeholder="Contoh: TIF101" autocomplete="off">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Mata Kuliah <span class="text-danger">*</span></label>
                <input type="text" name="name" required value="{{ old('name') }}"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="Contoh: Algoritma dan Pemrograman" autocomplete="off">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">SKS <span class="text-danger">*</span></label>
                <input type="number" name="sks" min="1" required value="{{ old('sks') }}"
                       class="form-control @error('sks') is-invalid @enderror">
                @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                <select name="semester" required class="form-select @error('semester') is-invalid @enderror">
                    <option value="" disabled {{ old('semester') ? '' : 'selected' }}>Pilih Semester</option>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                    @endfor
                </select>
                @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sifat <span class="text-danger">*</span></label>
                <select name="wajib_pilihan" required class="form-select">
                    <option value="W" {{ old('wajib_pilihan', 'W') === 'W' ? 'selected' : '' }}>Wajib (W)</option>
                    <option value="P" {{ old('wajib_pilihan') === 'P' ? 'selected' : '' }}>Pilihan (P)</option>
                </select>
            </div>

            <div class="mb-0">
                <label class="form-label fw-semibold">Mata Kuliah Prasyarat</label>
                <select name="prerequisite_course_id" class="form-select">
                    <option value="">Tidak Ada</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ old('prerequisite_course_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->code }} — {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="obe-card mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <h2 class="obe-card__title mb-1 d-flex align-items-center gap-2">
                        Daftar CPMK
                        <span class="badge bg-light text-dark border" id="cpmkTotalBadge">Total Bobot: 0%</span>
                    </h2>
                    <small class="text-muted">Tambahkan CPMK beserta indikatornya sebelum menyimpan.</small>
                </div>
                <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#cpmkBuilderModal"
                        onclick="openCpmkBuilder('create')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah CPMK
                </button>
            </div>

            <div id="cpmkListWrap"></div>

            <div id="cpmkEmptyState" class="text-center py-5 border border-dashed rounded">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted mb-2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-muted small mb-0">Belum ada CPMK yang ditambahkan.</p>
                <small class="text-muted">Klik tombol "Tambah CPMK" untuk menambahkan.</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-obe-red">Simpan Mata Kuliah</button>
            <a href="{{ route('courses.index') }}" class="btn btn-obe-outline">Batal</a>
        </div>

        <div id="cpmkHiddenInputs" style="display:none;"></div>
    </form>

    {{-- ── Modal: Tambah / Edit CPMK (sebelum MK disimpan) ─────── --}}
    <div class="modal fade" id="cpmkBuilderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cpmkBuilderTitle">Tambah CPMK Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height:calc(100vh - 220px); overflow-y:auto;">
                    <p class="text-muted small mb-3">Isi kode, CPL, pernyataan, dan indikator kinerja CPMK.</p>

                    <input type="hidden" id="cb_editIndex" value="-1">

                    <div class="border rounded p-3 mb-3">
                        <h6 class="fw-bold mb-3 text-uppercase" style="letter-spacing:.05em; font-size:.78rem; border-left:3px solid var(--obe-red); padding-left:.5rem;">Data CPMK</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kode CPMK <span class="text-danger">*</span></label>
                                <input type="text" id="cb_code" class="form-control" placeholder="Contoh: CPMK-01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">CPL yang Didukung <span class="text-danger">*</span></label>
                                <select id="cb_cpl" class="form-select">
                                    <option value="">-- Pilih CPL --</option>
                                    @foreach($cpls as $cpl)
                                        <option value="{{ $cpl->id }}" data-code="{{ $cpl->code }}">{{ $cpl->code }} — {{ Str::limit($cpl->description, 60) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bobot CPMK (%) <span class="text-danger">*</span></label>
                                <select id="cb_pct" class="form-select">
                                    <option value="">-- Pilih Bobot --</option>
                                    <option value="6.25">6,25% (1 Pertemuan)</option>
                                    <option value="12.5">12,5% (2 Pertemuan)</option>
                                    <option value="25">25% (4 Pertemuan)</option>
                                    <option value="50">50% (8 Pertemuan)</option>
                                    <option value="62.5">62,5% (10 Pertemuan)</option>
                                    <option value="75">75% (12 Pertemuan)</option>
                                    <option value="87.5">87,5% (14 Pertemuan)</option>
                                    <option value="100">100% (16 Pertemuan)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Pernyataan CPMK <span class="text-danger">*</span></label>
                                <textarea id="cb_desc" rows="3" class="form-control" placeholder="Tuliskan pernyataan capaian pembelajaran mata kuliah..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-uppercase" style="letter-spacing:.05em; font-size:.78rem; border-left:3px solid var(--obe-red); padding-left:.5rem;">Daftar Indikator Kinerja <span class="text-danger">*</span></h6>
                            <span class="badge" id="cb_indTotal" style="background:#fee2e2; color:#b91c1c;">Total: 0%</span>
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
                            <tbody id="cb_indList"></tbody>
                            <tfoot>
                                <tr style="background:#fef2f2;">
                                    <td class="text-danger fw-bold">+</td>
                                    <td><input type="text" id="cb_newDesc" class="form-control form-control-sm" placeholder="Deskripsi indikator baru..." onkeydown="if(event.key==='Enter'){event.preventDefault();cbAddInd();}"></td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.01" min="0" max="100" id="cb_newPct" class="form-control" placeholder="auto">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-obe-red" onclick="cbAddInd()" title="Tambah">+</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <small class="text-muted">Ketik pada baris merah lalu klik <strong>+</strong> atau tekan <strong>Enter</strong>. Kolom <strong>Bobot</strong> dikosongkan = otomatis (dibagi rata), diisi angka = manual. <strong>Total harus tepat 100%</strong> sebelum bisa disimpan.</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <span class="small text-muted"><span id="cb_indCount">0</span> indikator ditambahkan <span id="cb_indStatus" class="badge bg-light text-dark border ms-1"></span></span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-obe-red" id="cb_submit" onclick="saveCpmkFromBuilder()">✓ Tambahkan CPMK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $cplOptions = $cpls->map(fn($c) => ['id' => (string)$c->id, 'code' => $c->code])->values();
    @endphp

    <script>
        const CPL_OPTIONS = @json($cplOptions);

        // Master list of CPMKs added on this page
        let cpmks = []; // {code, cpl_id, percentage, description, indicators: [{description, percentage|null}]}
        let cbIndicators = []; // working indicator list inside modal

        const PCT_TO_PERTEMUAN = {6.25:1, 12.5:2, 25:4, 50:8, 62.5:10, 75:12, 87.5:14, 100:16};
        function pctToPertemuan(p) {
            const n = Number(p);
            return PCT_TO_PERTEMUAN[n] ?? '—';
        }

        function recalcCpmkTotal() {
            const total = cpmks.reduce((s, c) => s + Number(c.percentage || 0), 0);
            const badge = document.getElementById('cpmkTotalBadge');
            badge.textContent = 'Total Bobot: ' + total.toFixed(2) + '%';
            const ok = Math.abs(total - 100) < 0.01;
            badge.className = 'badge ' + (ok ? 'bg-success' : 'bg-danger') + ' text-white';

            const alertEl = document.getElementById('cpmkTotalAlert');
            if (cpmks.length === 0) {
                alertEl.classList.add('d-none');
                alertEl.textContent = '';
            } else if (!ok) {
                alertEl.classList.remove('d-none');
                const diff = total - 100;
                const msg = diff > 0
                    ? `Total bobot CPMK melebihi 100% (saat ini ${total.toFixed(2)}%, kelebihan ${diff.toFixed(2)}%). Mata kuliah tidak bisa ditambahkan.`
                    : `Total bobot CPMK kurang dari 100% (saat ini ${total.toFixed(2)}%, kurang ${(-diff).toFixed(2)}%). Mata kuliah tidak bisa ditambahkan.`;
                alertEl.textContent = msg;
            } else {
                alertEl.classList.add('d-none');
                alertEl.textContent = '';
            }
            return total;
        }

        function validateCourseSubmit(e) {
            if (cpmks.length === 0) {
                e.preventDefault();
                alert('Tambahkan minimal 1 CPMK sebelum menyimpan mata kuliah.');
                return false;
            }
            const total = cpmks.reduce((s, c) => s + Number(c.percentage || 0), 0);
            if (Math.abs(total - 100) > 0.01) {
                e.preventDefault();
                const diff = total - 100;
                const msg = diff > 0
                    ? `Total bobot CPMK melebihi 100% (saat ini ${total.toFixed(2)}%). Mata kuliah tidak bisa ditambahkan.`
                    : `Total bobot CPMK kurang dari 100% (saat ini ${total.toFixed(2)}%). Mata kuliah tidak bisa ditambahkan.`;
                alert(msg);
                document.getElementById('cpmkTotalAlert').scrollIntoView({behavior:'smooth', block:'center'});
                return false;
            }
            return true;
        }

        function renderCpmkList() {
            const wrap  = document.getElementById('cpmkListWrap');
            const empty = document.getElementById('cpmkEmptyState');
            const hidden = document.getElementById('cpmkHiddenInputs');

            if (cpmks.length === 0) {
                wrap.innerHTML = '';
                empty.style.display = '';
                hidden.innerHTML = '';
                recalcCpmkTotal();
                return;
            }
            empty.style.display = 'none';

            let html = '';
            cpmks.forEach((cp, idx) => {
                const cplCode = (CPL_OPTIONS.find(o => o.id === String(cp.cpl_id)) || {}).code || '—';
                const pertemuan = pctToPertemuan(cp.percentage);
                const indHtml = cp.indicators.map(i => {
                    const pct = i.percentage === null ? '(auto)' : Number(i.percentage).toFixed(2) + '%';
                    return `<li class="d-flex justify-content-between align-items-start gap-2 mb-1 small p-2 border rounded" style="background:#f8fafc;">
                        <span><span style="color:var(--obe-red);">•</span> ${escapeHtml(i.description)}</span>
                        <span class="badge bg-light text-dark border">${pct}</span>
                    </li>`;
                }).join('');
                html += `
                <div class="border rounded mb-2 overflow-hidden">
                    <div class="d-flex flex-wrap align-items-center gap-2 px-3 py-2" style="background:#f8fafc;">
                        <span class="badge bg-light text-dark border">${escapeHtml(cplCode)}</span>
                        <span class="badge" style="background:var(--obe-red); color:#fff;">${escapeHtml(cp.code)}</span>
                        <span class="badge bg-light text-dark border">${pertemuan} Pertemuan</span>
                        <span class="flex-grow-1 fw-semibold small">${escapeHtml(cp.description)}</span>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-obe-outline" data-bs-toggle="modal" data-bs-target="#cpmkBuilderModal" onclick="openCpmkBuilder('edit', ${idx})">Edit</button>
                            <button type="button" class="btn btn-sm btn-obe-red" onclick="removeCpmk(${idx})">Hapus</button>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="text-muted small text-uppercase fw-semibold mb-2" style="font-size:.7rem;">Indikator</div>
                        <ul class="list-unstyled mb-0">${indHtml || '<li class="text-muted small fst-italic">Tidak ada indikator.</li>'}</ul>
                    </div>
                </div>`;
            });
            wrap.innerHTML = html;

            // Hidden inputs for form submit (cpmks[N][...])
            let h = '';
            cpmks.forEach((cp, idx) => {
                h += `<input type="hidden" name="cpmks[${idx}][code]" value="${escapeAttr(cp.code)}">`;
                h += `<input type="hidden" name="cpmks[${idx}][cpl_id]" value="${escapeAttr(cp.cpl_id)}">`;
                h += `<input type="hidden" name="cpmks[${idx}][percentage]" value="${escapeAttr(cp.percentage)}">`;
                h += `<input type="hidden" name="cpmks[${idx}][description]" value="${escapeAttr(cp.description)}">`;
                cp.indicators.forEach((ind, j) => {
                    h += `<input type="hidden" name="cpmks[${idx}][indicators][${j}][description]" value="${escapeAttr(ind.description)}">`;
                    const p = ind.percentage === null ? '' : ind.percentage;
                    h += `<input type="hidden" name="cpmks[${idx}][indicators][${j}][percentage]" value="${escapeAttr(p)}">`;
                });
            });
            hidden.innerHTML = h;
            recalcCpmkTotal();
        }

        function removeCpmk(idx) {
            if (!confirm('Hapus CPMK ini?')) return;
            cpmks.splice(idx, 1);
            renderCpmkList();
        }

        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
        }
        function escapeAttr(s) { return escapeHtml(s); }

        // ── Modal builder ──
        function cbRecalcInd() {
            const tbody = document.getElementById('cb_indList');
            const totalEl = document.getElementById('cb_indTotal');
            const countEl = document.getElementById('cb_indCount');
            const statusEl = document.getElementById('cb_indStatus');

            const manualSum = cbIndicators.filter(i => i.percentage !== null).reduce((s, i) => s + Number(i.percentage), 0);
            const autoCount = cbIndicators.filter(i => i.percentage === null).length;
            const autoEach  = autoCount > 0 ? Math.max(0, (100 - manualSum) / autoCount) : 0;
            const total     = manualSum + (autoEach * autoCount);

            tbody.innerHTML = cbIndicators.map((ind, idx) => {
                const pct = ind.percentage === null ? autoEach : Number(ind.percentage);
                const isAuto = ind.percentage === null;
                const pctVal = ind.percentage === null ? '' : ind.percentage;
                const pctDisplay = pct.toFixed(2) + '%' + (isAuto ? ' <span class="text-muted fw-normal" style="font-size:.8em;">(auto)</span>' : '');
                return `
                <tr id="cb_indRow_${idx}">
                    <td class="text-muted small">${idx + 1}</td>
                    <td id="cb_indDescCell_${idx}">${escapeHtml(ind.description)}</td>
                    <td class="text-end fw-bold" style="color:var(--obe-red);" id="cb_indPctCell_${idx}">${pctDisplay}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary border-0" onclick="cbEditInd(${idx})" id="cb_btnEdit_${idx}" title="Edit">&#9998;</button>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="cbRemoveInd(${idx})" title="Hapus">&#128465;</button>
                        </div>
                    </td>
                </tr>`;
            }).join('');

            totalEl.textContent = 'Total: ' + total.toFixed(1) + '%';
            const ok = Math.abs(total - 100) < 0.01;
            totalEl.style.background = ok ? '#fee2e2' : '#fee2e2';
            totalEl.style.color      = ok ? '#b91c1c' : '#b91c1c';
            countEl.textContent = cbIndicators.length;
            statusEl.textContent = ok && cbIndicators.length > 0 ? 'Siap disimpan' : '';
        }

        function cbAddInd() {
            const desc = document.getElementById('cb_newDesc').value.trim();
            const pctRaw = document.getElementById('cb_newPct').value.trim();
            if (!desc) return;
            cbIndicators.push({ description: desc, percentage: pctRaw === '' ? null : Number(pctRaw) });
            document.getElementById('cb_newDesc').value = '';
            document.getElementById('cb_newPct').value = '';
            cbRecalcInd();
        }

        function cbRemoveInd(idx) {
            cbIndicators.splice(idx, 1);
            cbRecalcInd();
        }

        function cbSaveEditInd(idx) {
            const descEl = document.getElementById('cb_editDesc_' + idx);
            const pctEl  = document.getElementById('cb_editPct_' + idx);
            const desc   = descEl.value.trim();
            if (!desc) { descEl.focus(); return; }
            const pctRaw = pctEl.value.trim();
            cbIndicators[idx] = {
                description: desc,
                percentage: pctRaw === '' ? null : Number(pctRaw),
            };
            cbRecalcInd();
        }

        function openCpmkBuilder(mode, idx = -1) {
            document.getElementById('cb_editIndex').value = idx;
            const title = document.getElementById('cpmkBuilderTitle');
            const submit = document.getElementById('cb_submit');
            cbIndicators = [];

            if (mode === 'create') {
                title.textContent = 'Tambah CPMK Baru';
                submit.innerHTML  = '✓ Tambahkan CPMK';
                document.getElementById('cb_code').value = '';
                document.getElementById('cb_cpl').value  = '';
                document.getElementById('cb_pct').value  = '';
                document.getElementById('cb_desc').value = '';
            } else {
                const cp = cpmks[idx];
                title.textContent = 'Edit CPMK';
                submit.innerHTML  = '✓ Perbarui CPMK';
                document.getElementById('cb_code').value = cp.code;
                document.getElementById('cb_cpl').value  = cp.cpl_id;
                document.getElementById('cb_pct').value  = cp.percentage;
                document.getElementById('cb_desc').value = cp.description;
                cbIndicators = cp.indicators.map(i => ({...i}));
            }
            cbRecalcInd();
        }

        function cbEditInd(idx) {
            const ind = cbIndicators[idx];
            const pctVal = ind.percentage === null ? '' : ind.percentage;
            // Replace desc cell with input
            document.getElementById('cb_indDescCell_' + idx).innerHTML =
                `<input type="text" id="cb_editDesc_${idx}" class="form-control form-control-sm" value="${escapeAttr(ind.description)}" placeholder="Deskripsi indikator" onkeydown="if(event.key==='Enter'){event.preventDefault();cbSaveEditInd(${idx});}">`;
            // Replace pct cell with input
            document.getElementById('cb_indPctCell_' + idx).innerHTML =
                `<div class="input-group input-group-sm" style="max-width:120px;margin-left:auto;">
                    <input type="number" step="0.01" min="0" max="100" id="cb_editPct_${idx}" class="form-control text-end" value="${pctVal}" placeholder="auto" onkeydown="if(event.key==='Enter'){event.preventDefault();cbSaveEditInd(${idx});}">
                    <span class="input-group-text">%</span>
                </div>`;
            // Replace pencil button with save button
            document.getElementById('cb_btnEdit_' + idx).outerHTML =
                `<button type="button" class="btn btn-sm btn-obe-red" onclick="cbSaveEditInd(${idx})" id="cb_btnSave_${idx}" title="Simpan">&#10003;</button>`;
            document.getElementById('cb_editDesc_' + idx).focus();
        }

        function cbSaveEditInd(idx) {
            const descEl = document.getElementById('cb_editDesc_' + idx);
            const pctEl  = document.getElementById('cb_editPct_' + idx);
            if (!descEl) return; // not in edit mode
            const desc   = descEl.value.trim();
            if (!desc) { descEl.focus(); return; }
            const pctRaw = pctEl ? pctEl.value.trim() : '';
            cbIndicators[idx] = {
                description: desc,
                percentage: pctRaw === '' ? null : Number(pctRaw),
            };
            cbRecalcInd(); // re-renders all rows back to view mode
        }

        function saveCpmkFromBuilder() {
            const code = document.getElementById('cb_code').value.trim();
            const cpl  = document.getElementById('cb_cpl').value;
            const pct  = document.getElementById('cb_pct').value;
            const desc = document.getElementById('cb_desc').value.trim();

            if (!code || !cpl || pct === '' || !desc) {
                alert('Lengkapi data CPMK (kode, CPL, bobot, pernyataan).');
                return;
            }
            if (cbIndicators.length === 0) {
                alert('Tambahkan minimal 1 indikator kinerja.');
                return;
            }
            // Validate indicator total = 100
            const manualSum = cbIndicators.filter(i => i.percentage !== null).reduce((s, i) => s + Number(i.percentage), 0);
            const autoCount = cbIndicators.filter(i => i.percentage === null).length;
            const autoEach  = autoCount > 0 ? Math.max(0, (100 - manualSum) / autoCount) : 0;
            const total     = manualSum + (autoEach * autoCount);
            if (Math.abs(total - 100) > 0.01) {
                alert('Total bobot indikator harus 100%. Saat ini: ' + total.toFixed(2) + '%');
                return;
            }

            const payload = {
                code, cpl_id: cpl, percentage: Number(pct), description: desc,
                indicators: cbIndicators.map(i => ({...i})),
            };
            const idx = parseInt(document.getElementById('cb_editIndex').value);
            if (idx >= 0) cpmks[idx] = payload;
            else cpmks.push(payload);

            renderCpmkList();
            bootstrap.Modal.getInstance(document.getElementById('cpmkBuilderModal')).hide();
        }

        document.addEventListener('DOMContentLoaded', renderCpmkList);
    </script>
</x-sidebar-layout>