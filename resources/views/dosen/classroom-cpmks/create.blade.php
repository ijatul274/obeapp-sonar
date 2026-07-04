<x-sidebar-layout :title="'Buat CPMK'" :header="'Buat CPMK Baru'">

    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <p class="text-muted small mb-0">
            {{ $classroom->course?->code }} — {{ $classroom->name }} · {{ ucfirst($classroom->period_type ?? '') }} {{ $classroom->academic_year ?? '' }}
        </p>
        <a href="{{ route('dosen.classrooms.show', $classroom) }}" class="btn btn-obe-outline btn-sm">&larr; Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
    @endif

    @if($templates->isNotEmpty())
        <div class="alert alert-warning border-0 d-flex flex-column gap-2 mb-3" style="background:var(--obe-red-soft);">
            <div class="small fw-bold text-uppercase" style="color:var(--obe-red); letter-spacing:.05em;">Template CPMK dari Mata Kuliah (Referensi)</div>
            <div class="d-flex flex-wrap gap-1">
                @foreach($templates as $tmpl)
                    @php $tmplData = ['code' => $tmpl->code, 'desc' => $tmpl->description, 'cpl' => $tmpl->cpl_id, 'pct' => $tmpl->percentage]; @endphp
                    <button type="button" class="btn btn-sm btn-obe-outline d-inline-flex align-items-center gap-1"
                            onclick="fillTemplate({{ Js::from($tmplData) }})">
                        <span style="font-family:monospace;">{{ $tmpl->code }}</span>
                        @if($tmpl->cpl)<small class="text-muted">{{ $tmpl->cpl->code }}</small>@endif
                    </button>
                @endforeach
            </div>
            <small class="text-muted">Klik template untuk mengisi otomatis. Anda tetap dapat mengubahnya.</small>
        </div>
    @endif

    <form method="POST" action="{{ route('dosen.classroom-cpmks.store', $classroom) }}" id="cpmkForm"
          x-data="{
              indicatorWeightType: 'otomatis',
              indicators: [{desc:'', pct:''}],
              addIndicator() { this.indicators.push({desc:'', pct:''}); },
              removeIndicator(i) { if (this.indicators.length > 1) this.indicators.splice(i,1); }
          }">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">Kode CPMK <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="fieldCode" required value="{{ old('code') }}" class="form-control" style="font-family:monospace;" placeholder="CPMK-1">
                </div>
            </div>

            <div class="col-md-6">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">Bobot (%) <span class="text-danger">*</span></label>
                    <input type="number" name="percentage" id="fieldPct" required min="1" max="100" step="0.01" value="{{ old('percentage', 25) }}" class="form-control">
                    <div class="form-text">Total bobot semua CPMK harus 100%.</div>
                </div>
            </div>

            <div class="col-12">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">CPL Terkait <span class="text-danger">*</span></label>
                    <select name="cpl_id" id="fieldCpl" required class="form-select">
                        <option value="">— Pilih CPL —</option>
                        @foreach($cpls as $cpl)
                            <option value="{{ $cpl->id }}" {{ old('cpl_id') == $cpl->id ? 'selected' : '' }}>
                                {{ $cpl->code }} — {{ Str::limit($cpl->description, 70) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12">
                <div class="obe-card">
                    <label class="form-label fw-semibold text-uppercase small" style="font-size:.7rem;">Deskripsi CPMK <span class="text-danger">*</span></label>
                    <textarea name="description" id="fieldDesc" rows="3" required class="form-control" placeholder="Tuliskan kemampuan akhir yang diharapkan...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="col-12">
                <div class="obe-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="obe-card__title mb-0">Sub-CPMK Pencapaian</h2>
                            <small class="text-muted">Tambahkan Sub-CPMK yang mengukur CPMK ini.</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label small mb-0">Bobot:</label>
                            <select name="indicator_weight_type" x-model="indicatorWeightType" class="form-select form-select-sm" style="width:auto;">
                                <option value="otomatis">Otomatis (rata)</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                    </div>

                    <template x-for="(ind, i) in indicators" :key="i">
                        <div class="border rounded p-3 mb-2" style="background:var(--obe-bg);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold" style="color:var(--obe-red);" x-text="'Sub-CPMK ' + (i+1)"></span>
                                <button type="button" @click="removeIndicator(i)" x-show="indicators.length > 1" class="btn btn-sm btn-obe-outline">Hapus</button>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="text" :name="'indicator_descriptions[' + i + ']'" x-model="ind.desc" placeholder="Deskripsi Sub-CPMK..." class="form-control form-control-sm flex-grow-1">
                                <input type="number" :name="'indicator_percentages[' + i + ']'" x-model="ind.pct" x-show="indicatorWeightType === 'manual'" placeholder="%" min="0" max="100" step="0.01" class="form-control form-control-sm text-center" style="width:90px;">
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addIndicator()" class="btn btn-obe-outline btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2 border-dashed">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Sub-CPMK
                    </button>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="submit" name="submit_action" value="draft" class="btn btn-obe-outline flex-grow-1">Simpan sebagai Draft</button>
            <button type="submit" name="submit_action" value="submit" class="btn btn-obe-red flex-grow-1">Simpan & Kirim untuk Persetujuan</button>
        </div>
    </form>

<script>
function fillTemplate(data) {
    document.getElementById('fieldCode').value = data.code || '';
    document.getElementById('fieldDesc').value = data.desc || '';
    if (data.pct) document.getElementById('fieldPct').value = data.pct;
    if (data.cpl) document.getElementById('fieldCpl').value = data.cpl;
}
</script>
</x-sidebar-layout>