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
                    <h2 class="obe-card__title mb-1">CPL yang Dibebankan ke Mata Kuliah Ini</h2>
                    <small class="text-muted">Pilih CPL yang dibebankan ke MK ini. CPMK di bawah hanya boleh merujuk ke salah satu CPL yang dipilih di sini, sesuai dokumen kurikulum.</small>
                </div>
                <span class="badge bg-light text-dark border" id="cplSelectedBadge">0 dipilih</span>
            </div>
            @error('cpl_ids')<div class="alert alert-danger small mb-2">{{ $message }}</div>@enderror
            @if($cpls->isEmpty())
                <div class="alert alert-warning small mb-0">Belum ada CPL pada program studi ini. Tambahkan CPL terlebih dahulu sebelum membuat mata kuliah.</div>
            @else
                <div class="border rounded" style="max-height:260px; overflow-y:auto;">
                    @foreach($cpls as $cpl)
                        <label class="d-flex align-items-start gap-2 p-2 border-bottom small mb-0" style="cursor:pointer;">
                            <input type="checkbox" name="cpl_ids[]" value="{{ $cpl->id }}"
                                   class="form-check-input mt-1 cpl-checkbox" onchange="onCplToggle(this)"
                                   {{ in_array($cpl->id, old('cpl_ids', [])) ? 'checked' : '' }}>
                            <span><span class="badge" style="background:var(--obe-red);color:#fff;">{{ $cpl->code }}</span> {{ $cpl->description }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="obe-card mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <h2 class="obe-card__title mb-1 d-flex align-items-center gap-2">
                        Daftar CPMK
                        <span class="badge bg-light text-dark border" id="cpmkTotalBadge">Total Bobot: 0%</span>
                    </h2>
                    <small class="text-muted">Tambahkan CPMK beserta Sub-CPMK-nya sebelum menyimpan.</small>
                </div>
                <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                        onclick="tryOpenCpmkBuilder('create')">
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
                    <p class="text-muted small mb-3">Isi kode, CPL, pernyataan, dan Sub-CPMK dari CPMK.</p>

                    <input type="hidden" id="cb_editIndex" value="-1">

                    <div class="border rounded p-3 mb-3">
                        <h6 class="fw-bold mb-3 text-uppercase" style="letter-spacing:.05em; font-size:.78rem; border-left:3px solid var(--obe-red); padding-left:.5rem;">Data CPMK</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kode CPMK <span class="text-danger">*</span></label>
                                <input type="text" id="cb_code" class="form-control" placeholder="Contoh: CPMK-01">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">CPL yang Didukung <span class="text-danger">*</span></label>
                                <select id="cb_cpl" class="form-select">
                                    <option value="">-- Pilih CPL --</option>
                                </select>
                                <small class="text-muted">Hanya menampilkan CPL yang dicentang di bagian "CPL yang Dibebankan".</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Pernyataan CPMK <span class="text-danger">*</span></label>
                                <textarea id="cb_desc" rows="3" class="form-control" placeholder="Tuliskan pernyataan capaian pembelajaran mata kuliah..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0 text-uppercase" style="letter-spacing:.05em; font-size:.78rem; border-left:3px solid var(--obe-red); padding-left:.5rem;">Sub-CPMK &amp; Indikator <span class="text-danger">*</span></h6>
                            <span class="badge" id="cb_subTotal" style="background:#fee2e2; color:#b91c1c;">Total Sub: 0%</span>
                        </div>
                        <p class="text-muted small mb-2">Tiap CPMK terdiri dari beberapa <strong>Sub-CPMK</strong>; tiap Sub-CPMK punya beberapa <strong>Indikator</strong> (alat ukur). <strong>Bobot Sub-CPMK dihitung otomatis dari jumlah pertemuannya</strong> (proporsional). Anda cukup menentukan <strong>indikator apa saja</strong> yang dinilai — <strong>bobot tiap indikator ditentukan oleh dosen pengampu</strong> saat mengajar.</p>

                        <div id="cb_subList" class="d-flex flex-column gap-2 mb-2"></div>

                        <div class="d-flex gap-2 align-items-end border-top pt-2">
                            <div class="flex-grow-1">
                                <label class="form-label small mb-1 fw-semibold">Tambah Sub-CPMK</label>
                                <input type="text" id="cb_newSubDesc" class="form-control form-control-sm" placeholder="Deskripsi Sub-CPMK..." onkeydown="if(event.key==='Enter'){event.preventDefault();cbAddSub();}">
                            </div>
                            <div style="width:130px;">
                                <label class="form-label small mb-1 fw-semibold">Pertemuan <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="number" min="1" max="16" id="cb_newSubMeet" class="form-control" placeholder="1" title="Jumlah pertemuan untuk Sub-CPMK ini — menentukan bobotnya">
                                    <span class="input-group-text">&times;</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-obe-red" onclick="cbAddSub()">+ Sub</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <span class="small text-muted">Bobot Sub-CPMK dihitung dari pertemuan; bobot Indikator diatur dosen pengampu.</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-obe-red" id="cb_submit" onclick="saveCpmkFromBuilder()">✓ Tambahkan CPMK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $cplOptions = $cpls->map(fn($c) => ['id' => (string)$c->id, 'code' => $c->code, 'desc' => \Illuminate\Support\Str::limit($c->description, 60)])->values();
    @endphp

    <script>
        const CPL_OPTIONS = @json($cplOptions);

        // Master list of CPMKs added on this page
        // {code, cpl_id, percentage, description, subcpmks:[{description, percentage|null, meetings|null, indicators:[{description, percentage|null}]}]}
        let cpmks = [];
        let cbSubs = []; // working Sub-CPMK list inside the modal

        // Bagi 100%: manual dipakai apa adanya, null dibagi rata sisa.
        function cbResolve(pcts) {
            const manual = pcts.filter(p => p !== null).reduce((s, p) => s + Number(p), 0);
            const autoN = pcts.filter(p => p === null).length;
            const autoEach = autoN > 0 ? Math.max(0, (100 - manual) / autoN) : 0;
            return { display: pcts.map(p => p === null ? autoEach : Number(p)), total: manual + autoEach * autoN };
        }

        // Bobot Sub-CPMK proporsional dari jumlah pertemuan (kosong dianggap 1).
        function cbResolveMeetings(meets) {
            const counts = meets.map(m => (m === null || m === undefined || Number(m) < 1) ? 1 : Number(m));
            const total = counts.reduce((s, c) => s + c, 0);
            if (total <= 0) return { display: counts.map(() => 0), total: 0 };
            return { display: counts.map(c => c / total * 100), total: 100 };
        }

        // ── CPL yang dibebankan ke MK ──
        function selectedCplIds() {
            return Array.from(document.querySelectorAll('.cpl-checkbox:checked')).map(c => c.value);
        }

        function refreshCplSelection() {
            const ids = selectedCplIds();
            const badge = document.getElementById('cplSelectedBadge');
            if (badge) badge.textContent = ids.length + ' dipilih';

            const sel = document.getElementById('cb_cpl');
            if (sel) {
                const current = sel.value;
                let html = '<option value="">-- Pilih CPL --</option>';
                ids.forEach(id => {
                    const o = CPL_OPTIONS.find(x => x.id === String(id));
                    if (o) html += `<option value="${o.id}">${escapeHtml(o.code)} — ${escapeHtml(o.desc)}</option>`;
                });
                sel.innerHTML = html;
                if (ids.includes(current)) sel.value = current;
            }
        }

        function onCplToggle(cb) {
            if (!cb.checked) {
                const used = cpmks.some(cp => String(cp.cpl_id) === String(cb.value));
                if (used) {
                    alert('CPL ini masih dipakai oleh salah satu CPMK. Hapus CPMK terkait dulu sebelum membatalkan CPL.');
                    cb.checked = true;
                    return;
                }
            }
            refreshCplSelection();
        }

        // Buka modal CPMK hanya jika minimal 1 CPL sudah dipilih
        function tryOpenCpmkBuilder(mode, idx = -1) {
            if (selectedCplIds().length === 0) {
                alert('Pilih minimal 1 CPL yang dibebankan ke MK terlebih dahulu sebelum menambah CPMK.');
                return;
            }
            refreshCplSelection();
            openCpmkBuilder(mode, idx);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('cpmkBuilderModal')).show();
        }

        // Bagi 100% rata ke n CPMK; sisa pembulatan ditambahkan ke item terakhir.
        function cbEqualWeights(n) {
            if (n <= 0) return [];
            const base = Math.floor((100 / n) * 100) / 100;
            const rem  = Math.round((100 - base * n) * 100) / 100;
            const w = Array(n).fill(base);
            w[n - 1] = Math.round((base + rem) * 100) / 100;
            return w;
        }

        function recalcCpmkTotal() {
            const badge = document.getElementById('cpmkTotalBadge');
            const n = cpmks.length;
            badge.textContent = n > 0
                ? `${n} CPMK · bobot dibagi rata otomatis`
                : 'Belum ada CPMK';
            badge.className = 'badge bg-light text-dark border';
            document.getElementById('cpmkTotalAlert').classList.add('d-none');
        }

        function validateCourseSubmit(e) {
            const selIds = selectedCplIds();
            if (selIds.length === 0) {
                e.preventDefault();
                alert('Pilih minimal 1 CPL yang dibebankan ke mata kuliah ini.');
                return false;
            }
            const orphan = cpmks.find(cp => !selIds.includes(String(cp.cpl_id)));
            if (orphan) {
                e.preventDefault();
                alert('CPMK "' + orphan.code + '" merujuk CPL yang tidak lagi dipilih. Perbaiki CPMK atau pilih kembali CPL tersebut.');
                return false;
            }
            if (cpmks.length === 0) {
                e.preventDefault();
                alert('Tambahkan minimal 1 CPMK sebelum menyimpan mata kuliah.');
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

            // Bobot CPMK dibagi rata otomatis (tidak lagi diisi manual).
            const eqWeights = cbEqualWeights(cpmks.length);
            cpmks.forEach((cp, idx) => { cp.percentage = eqWeights[idx]; });

            let html = '';
            cpmks.forEach((cp, idx) => {
                const cplCode = (CPL_OPTIONS.find(o => o.id === String(cp.cpl_id)) || {}).code || '—';
                const subRes = cbResolveMeetings(cp.subcpmks.map(s => s.meetings));
                const subsHtml = cp.subcpmks.map((sub, si) => {
                    const inds = sub.indicators.map((ind, ii) =>
                        `<li class="d-flex justify-content-between align-items-center gap-2 small py-1 ps-3 pe-2">
                            <span><span style="color:#7c3aed;">&#9656;</span> ${escapeHtml(ind.description)}</span>
                                                    </li>`).join('') || '<li class="text-muted small fst-italic ps-3 py-1">Belum ada indikator.</li>';
                    return `<div class="border rounded mb-1" style="background:#f8fafc;">
                        <div class="d-flex justify-content-between align-items-center gap-2 px-2 py-1 border-bottom">
                            <span class="small fw-semibold"><span class="badge bg-success me-1">Sub ${si + 1}</span>${escapeHtml(sub.description)}</span>
                            <span class="d-flex align-items-center gap-1">
                                ${sub.meetings ? `<span class="badge bg-light text-dark border">${sub.meetings}&times;</span>` : ''}
                                <span class="badge" style="background:#dcfce7; color:#166534;">${subRes.display[si].toFixed(1)}%</span>
                            </span>
                        </div>
                        <ul class="list-unstyled mb-1 mt-1">${inds}</ul>
                    </div>`;
                }).join('') || '<div class="text-muted small fst-italic">Tidak ada Sub-CPMK.</div>';
                html += `
                <div class="border rounded mb-2 overflow-hidden">
                    <div class="d-flex flex-wrap align-items-center gap-2 px-3 py-2" style="background:#f8fafc;">
                        <span class="badge bg-light text-dark border">${escapeHtml(cplCode)}</span>
                        <span class="badge" style="background:var(--obe-red); color:#fff;">${escapeHtml(cp.code)}</span>
                        <span class="badge bg-light text-dark border" title="Bobot CPMK dibagi rata otomatis">${Number(cp.percentage).toFixed(2)}% <span class="text-muted">(otomatis)</span></span>
                        <span class="flex-grow-1 fw-semibold small">${escapeHtml(cp.description)}</span>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-obe-outline" onclick="tryOpenCpmkBuilder('edit', ${idx})">Edit</button>
                            <button type="button" class="btn btn-sm btn-obe-red" onclick="removeCpmk(${idx})">Hapus</button>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="text-muted small text-uppercase fw-semibold mb-2" style="font-size:.7rem;">Sub-CPMK &amp; Indikator</div>
                        ${subsHtml}
                    </div>
                </div>`;
            });
            wrap.innerHTML = html;

            // Hidden inputs for form submit (cpmks[N][subcpmks][i][...])
            let h = '';
            cpmks.forEach((cp, idx) => {
                h += `<input type="hidden" name="cpmks[${idx}][code]" value="${escapeAttr(cp.code)}">`;
                h += `<input type="hidden" name="cpmks[${idx}][cpl_id]" value="${escapeAttr(cp.cpl_id)}">`;
                h += `<input type="hidden" name="cpmks[${idx}][percentage]" value="${escapeAttr(cp.percentage)}">`;
                h += `<input type="hidden" name="cpmks[${idx}][description]" value="${escapeAttr(cp.description)}">`;
                cp.subcpmks.forEach((sub, si) => {
                    h += `<input type="hidden" name="cpmks[${idx}][subcpmks][${si}][description]" value="${escapeAttr(sub.description)}">`;
                    h += `<input type="hidden" name="cpmks[${idx}][subcpmks][${si}][percentage]" value="${sub.percentage === null ? '' : sub.percentage}">`;
                    h += `<input type="hidden" name="cpmks[${idx}][subcpmks][${si}][meetings]" value="${sub.meetings === null || sub.meetings === undefined ? '' : sub.meetings}">`;
                    sub.indicators.forEach((ind, ii) => {
                        h += `<input type="hidden" name="cpmks[${idx}][subcpmks][${si}][indicators][${ii}][description]" value="${escapeAttr(ind.description)}">`;
                        h += `<input type="hidden" name="cpmks[${idx}][subcpmks][${si}][indicators][${ii}][percentage]" value="${ind.percentage === null ? '' : ind.percentage}">`;
                    });
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

        // ── Modal builder: nested Sub-CPMK → Indikator ──
        function cbRenderSubs() {
            const list = document.getElementById('cb_subList');
            const subRes = cbResolveMeetings(cbSubs.map(s => s.meetings));

            list.innerHTML = cbSubs.map((sub, si) => {
                const indRows = sub.indicators.map((ind, ii) => `
                    <li class="d-flex justify-content-between align-items-center gap-2 small py-1 ps-3 pe-2">
                        <span><span style="color:#7c3aed;">&#9656;</span> ${escapeHtml(ind.description)}</span>
                        <span class="d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0" onclick="cbRemoveInd(${si},${ii})" title="Hapus indikator">&#128465;</button>
                        </span>
                    </li>`).join('') || '<li class="text-muted small fst-italic ps-3 py-1">Belum ada indikator.</li>';

                return `
                <div class="border rounded" style="background:#f8fafc;">
                    <div class="d-flex justify-content-between align-items-center gap-2 px-2 py-2 border-bottom">
                        <span class="fw-semibold small"><span class="badge bg-success me-1">Sub ${si + 1}</span>${escapeHtml(sub.description)}</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border">${sub.meetings ? sub.meetings : 1}&times; pertemuan</span>
                            <span class="badge" style="background:#dcfce7; color:#166534;" title="Bobot otomatis dari pertemuan">${subRes.display[si].toFixed(2)}%</span>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0" onclick="cbRemoveSub(${si})" title="Hapus Sub-CPMK">&#128465;</button>
                        </span>
                    </div>
                    <ul class="list-unstyled mb-1 mt-1">${indRows}</ul>
                    <div class="d-flex gap-2 align-items-center px-2 pb-2">
                        <input type="text" class="form-control form-control-sm" id="cb_newIndDesc_${si}" placeholder="Indikator baru (alat ukur)..." onkeydown="if(event.key==='Enter'){event.preventDefault();cbAddInd(${si});}">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="cbAddInd(${si})" title="Tambah indikator">+ Indikator</button>
                    </div>
                </div>`;
            }).join('') || '<p class="text-muted small fst-italic mb-0">Belum ada Sub-CPMK. Tambahkan di bawah.</p>';

            const totalEl = document.getElementById('cb_subTotal');
            const ok = Math.abs(subRes.total - 100) < 0.01 || cbSubs.length === 0;
            totalEl.textContent = 'Total Sub: ' + subRes.total.toFixed(1) + '%';
            totalEl.style.background = ok ? '#dcfce7' : '#fee2e2';
            totalEl.style.color = ok ? '#166534' : '#b91c1c';
        }

        function cbAddSub() {
            const d = document.getElementById('cb_newSubDesc');
            const m = document.getElementById('cb_newSubMeet');
            const desc = d.value.trim();
            if (!desc) return;
            const meet = m.value.trim();
            cbSubs.push({ description: desc, percentage: null, meetings: meet === '' ? null : Number(meet), indicators: [] });
            d.value = ''; m.value = '';
            cbRenderSubs();
        }

        function cbRemoveSub(si) { cbSubs.splice(si, 1); cbRenderSubs(); }

        function cbAddInd(si) {
            const d = document.getElementById('cb_newIndDesc_' + si);
            const desc = d.value.trim();
            if (!desc) return;
            cbSubs[si].indicators.push({ description: desc, percentage: null });
            cbRenderSubs();
        }

        function cbRemoveInd(si, ii) { cbSubs[si].indicators.splice(ii, 1); cbRenderSubs(); }

        function openCpmkBuilder(mode, idx = -1) {
            refreshCplSelection();
            document.getElementById('cb_editIndex').value = idx;
            const title = document.getElementById('cpmkBuilderTitle');
            const submit = document.getElementById('cb_submit');
            cbSubs = [];

            if (mode === 'create') {
                title.textContent = 'Tambah CPMK Baru';
                submit.innerHTML  = '✓ Tambahkan CPMK';
                document.getElementById('cb_code').value = '';
                document.getElementById('cb_cpl').value  = '';
                document.getElementById('cb_desc').value = '';
            } else {
                const cp = cpmks[idx];
                title.textContent = 'Edit CPMK';
                submit.innerHTML  = '✓ Perbarui CPMK';
                document.getElementById('cb_code').value = cp.code;
                document.getElementById('cb_cpl').value  = cp.cpl_id;
                document.getElementById('cb_desc').value = cp.description;
                cbSubs = (cp.subcpmks || []).map(s => ({
                    description: s.description,
                    percentage: s.percentage,
                    meetings: s.meetings ?? null,
                    indicators: (s.indicators || []).map(i => ({ description: i.description, percentage: i.percentage })),
                }));
            }
            cbRenderSubs();
        }

        function saveCpmkFromBuilder() {
            const code = document.getElementById('cb_code').value.trim();
            const cpl  = document.getElementById('cb_cpl').value;
            const desc = document.getElementById('cb_desc').value.trim();

            if (!code || !cpl || !desc) {
                alert('Lengkapi data CPMK (kode, CPL, pernyataan).');
                return;
            }
            if (cbSubs.length === 0) {
                alert('Tambahkan minimal 1 Sub-CPMK.');
                return;
            }

            const payload = {
                code, cpl_id: cpl, percentage: null, description: desc,
                subcpmks: cbSubs.map(s => ({
                    description: s.description,
                    percentage: s.percentage,
                    meetings: s.meetings ?? null,
                    indicators: s.indicators.map(i => ({...i})),
                })),
            };
            const idx = parseInt(document.getElementById('cb_editIndex').value);
            if (idx >= 0) cpmks[idx] = payload;
            else cpmks.push(payload);

            renderCpmkList();
            bootstrap.Modal.getInstance(document.getElementById('cpmkBuilderModal')).hide();
        }

        document.addEventListener('DOMContentLoaded', function () {
            refreshCplSelection();
            renderCpmkList();
        });
    </script>
</x-sidebar-layout>