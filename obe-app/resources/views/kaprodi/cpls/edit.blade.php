<x-sidebar-layout :title="'Edit CPL'" :header="'Edit CPL'">
    <div class="obe-card">
        <form method="POST" action="{{ route('cpls.update', $cpl) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Kode CPL <span class="text-danger">*</span></label>
                <input type="text" name="code" value="{{ old('code', $cpl->code) }}" required autocomplete="off"
                       class="form-control @error('code') is-invalid @enderror">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Pernyataan Capaian Pembelajaran Lulusan <span class="text-danger">*</span></label>
                <textarea name="description" rows="4" required class="form-control @error('description') is-invalid @enderror">{{ old('description', $cpl->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Minimal Ketercapaian (%) <span class="text-danger">*</span></label>
                <div class="input-group" style="max-width: 200px;">
                    <input type="number" name="min_target" value="{{ old('min_target', $cpl->min_target) }}"
                           required min="0" max="100" step="0.01"
                           placeholder="Contoh: 60"
                           class="form-control @error('min_target') is-invalid @enderror">
                    <span class="input-group-text">%</span>
                    @error('min_target')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text">Nilai antara 0 – 100.</div>
            </div>
            <div class="d-flex gap-2 pt-2 border-top">
                <button type="submit" class="btn btn-obe-red">Perbarui</button>
                <a href="{{ route('cpls.index') }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
    </div>
</x-sidebar-layout>
