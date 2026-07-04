<x-sidebar-layout :title="'Edit CPMK'" :header="'Edit CPMK ' . $cpmk->code">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted small mb-0">Mata Kuliah: <span class="fw-semibold">{{ $course->code }} — {{ $course->name }}</span></p>
        <a href="{{ route('courses.show', $course) }}" class="text-decoration-none small text-muted">&larr; Kembali</a>
    </div>

    <div class="obe-card">
        <form method="POST" action="{{ route('cpmks.update', $cpmk) }}">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode CPMK <span class="text-danger">*</span></label>
                    <input type="text" name="code" required value="{{ old('code', $cpmk->code) }}" class="form-control @error('code') is-invalid @enderror">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bobot CPMK (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" name="percentage" required value="{{ old('percentage', $cpmk->percentage) }}" class="form-control @error('percentage') is-invalid @enderror">
                    @error('percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">CPL yang Didukung <span class="text-danger">*</span></label>
                    <select name="cpl_id" required class="form-select @error('cpl_id') is-invalid @enderror">
                        <option value="">— Pilih CPL —</option>
                        @foreach($cpls as $cpl)
                            <option value="{{ $cpl->id }}" {{ old('cpl_id', $cpmk->cpl_id) == $cpl->id ? 'selected' : '' }}>
                                {{ $cpl->code }} — {{ Str::limit($cpl->description, 80) }}
                            </option>
                        @endforeach
                    </select>
                    @error('cpl_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Pernyataan CPMK <span class="text-danger">*</span></label>
                    <textarea name="description" rows="4" required class="form-control @error('description') is-invalid @enderror">{{ old('description', $cpmk->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Dosen Pengampu</label>
                    <select name="lecturer_id" class="form-select">
                        <option value="">— Tidak ada dosen spesifik —</option>
                        @foreach($lecturers as $l)
                            <option value="{{ $l->id }}" {{ old('lecturer_id', $cpmk->lecturer_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
                    <h3 class="h6 fw-bold mb-0">Indikator Kinerja <span class="text-danger">*</span></h3>
                    <div class="border rounded p-1 d-inline-flex" style="background:var(--obe-bg);">
                        @php
                            $weightType = old('indicator_weight_type', 'manual'); // edit existing → asume manual
                        @endphp
                        <div class="form-check form-check-inline me-3 ms-2">
                            <input type="radio" name="indicator_weight_type" value="otomatis" id="iwt-auto" class="form-check-input" {{ $weightType === 'otomatis' ? 'checked' : '' }} onchange="toggleIndicatorWeightType()">
                            <label for="iwt-auto" class="form-check-label small">Otomatis</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" name="indicator_weight_type" value="manual" id="iwt-manual" class="form-check-input" {{ $weightType === 'manual' ? 'checked' : '' }} onchange="toggleIndicatorWeightType()">
                            <label for="iwt-manual" class="form-check-label small">Manual</label>
                        </div>
                    </div>
                </div>

                <div id="indicators-list">
                    @php
                        $oldDescriptions = old('indicator_descriptions') ?? $cpmk->indicators->pluck('description')->toArray();
                        $oldPercentages  = old('indicator_percentages')  ?? $cpmk->indicators->pluck('percentage')->toArray();
                        if (empty($oldDescriptions)) $oldDescriptions = ['', '', ''];
                    @endphp
                    @foreach($oldDescriptions as $i => $desc)
                        <div class="row g-2 mb-2 indicator-row">
                            <div class="col-md-9">
                                <input type="text" name="indicator_descriptions[]" placeholder="Deskripsi Indikator {{ $i+1 }}" class="form-control indicator-desc-input" value="{{ $desc }}">
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="indicator_percentages[]" placeholder="Bobot" class="form-control indicator-weight-input" value="{{ $oldPercentages[$i] ?? '' }}" oninput="calculateTotalIndicatorWeight()">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex">
                                <button type="button" class="btn btn-sm btn-obe-outline w-100" onclick="removeIndicatorRow(this)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-obe-outline btn-sm d-inline-flex align-items-center gap-2" onclick="addIndicatorRow()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Indikator
                    </button>
                    <span class="badge bg-light text-dark border px-3 py-2" id="total-indicator-weight">Total: 0%</span>
                </div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top mt-4">
                <button type="submit" class="btn btn-obe-red">Perbarui</button>
                <a href="{{ route('courses.show', $course) }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
    </div>

<script>
function toggleIndicatorWeightType() {
    const t = document.querySelector('input[name="indicator_weight_type"]:checked').value;
    const inputs = document.querySelectorAll('.indicator-weight-input');
    if (t === 'otomatis') {
        inputs.forEach(i => { i.readOnly = true; i.classList.add('bg-light'); });
        updateOtomatisWeights();
    } else {
        inputs.forEach(i => { i.readOnly = false; i.classList.remove('bg-light'); });
        calculateTotalIndicatorWeight();
    }
}
function updateOtomatisWeights() {
    if (document.querySelector('input[name="indicator_weight_type"]:checked').value !== 'otomatis') return;
    const rows = document.querySelectorAll('.indicator-row');
    if (!rows.length) return;
    const avg = (100 / rows.length).toFixed(2);
    document.querySelectorAll('.indicator-weight-input').forEach(i => i.value = avg);
    document.getElementById('total-indicator-weight').innerText = 'Total: 100%';
}
function calculateTotalIndicatorWeight() {
    if (document.querySelector('input[name="indicator_weight_type"]:checked').value === 'otomatis') return;
    let total = 0;
    document.querySelectorAll('.indicator-weight-input').forEach(i => total += parseFloat(i.value) || 0);
    const div = document.getElementById('total-indicator-weight');
    div.innerText = 'Total: ' + total.toFixed(2) + '%';
    div.style.color = Math.abs(total - 100) < 0.01 ? '#16a34a' : 'var(--obe-red)';
}
function addIndicatorRow() {
    const list = document.getElementById('indicators-list');
    const n = list.querySelectorAll('.indicator-row').length + 1;
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 indicator-row';
    row.innerHTML = `
        <div class="col-md-9"><input type="text" name="indicator_descriptions[]" placeholder="Deskripsi Indikator ${n}" class="form-control indicator-desc-input"></div>
        <div class="col-md-2"><div class="input-group"><input type="number" step="0.01" min="0" max="100" name="indicator_percentages[]" placeholder="Bobot" class="form-control indicator-weight-input" oninput="calculateTotalIndicatorWeight()"><span class="input-group-text">%</span></div></div>
        <div class="col-md-1 d-flex"><button type="button" class="btn btn-sm btn-obe-outline w-100" onclick="removeIndicatorRow(this)">×</button></div>`;
    list.appendChild(row);
    toggleIndicatorWeightType();
    row.querySelector('.indicator-desc-input').addEventListener('input', updateOtomatisWeights);
}
function removeIndicatorRow(btn) {
    if (document.querySelectorAll('.indicator-row').length <= 1) { alert('Minimal 1 indikator.'); return; }
    btn.closest('.indicator-row').remove();
    toggleIndicatorWeightType();
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('form').addEventListener('submit', e => {
        const t = document.querySelector('input[name="indicator_weight_type"]:checked').value;
        if (t === 'manual') {
            let total = 0;
            document.querySelectorAll('.indicator-weight-input').forEach(i => total += parseFloat(i.value) || 0);
            if (Math.abs(total - 100) > 0.01) { e.preventDefault(); alert('Total bobot indikator (Manual) harus 100%. Saat ini: ' + total.toFixed(2) + '%'); }
        }
    });
    document.querySelectorAll('.indicator-desc-input').forEach(i => i.addEventListener('input', updateOtomatisWeights));
    calculateTotalIndicatorWeight();
});
</script>
</x-sidebar-layout>
