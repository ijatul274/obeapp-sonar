<x-sidebar-layout :title="'Kelola Akademik'" :header="'Kelola Akademik'">

    @include('partials._kelola_akademik_nav')

    <p class="text-muted small mb-3">
        Pengaturan semester yang sedang berjalan. Sistem otomatis membuat record baru saat memasuki periode semester berikutnya.
    </p>

    <div class="obe-card">
        <form method="POST" action="{{ route('admin.semester.update', $semester) }}">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                    <select name="periode" required
                            class="form-select @error('periode') is-invalid @enderror">
                        <option value="ganjil" {{ old('periode', $semester->periode) === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="genap"  {{ old('periode', $semester->periode) === 'genap'  ? 'selected' : '' }}>Genap</option>
                    </select>
                    @error('periode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                    <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $semester->tahun_ajaran) }}"
                           required pattern="\d{4}/\d{4}" placeholder="2025/2026"
                           class="form-control @error('tahun_ajaran') is-invalid @enderror" style="font-family:monospace;">
                    @error('tahun_ajaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai"
                           value="{{ old('tanggal_mulai', $semester->tanggal_mulai->format('Y-m-d')) }}"
                           required class="form-control @error('tanggal_mulai') is-invalid @enderror">
                    <div class="form-text">Format tampilan: DD/MM/YYYY (input pakai picker browser).</div>
                    @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_selesai"
                           value="{{ old('tanggal_selesai', $semester->tanggal_selesai->format('Y-m-d')) }}"
                           required class="form-control @error('tanggal_selesai') is-invalid @enderror">
                    @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="alert alert-light border d-flex align-items-start gap-2 mt-4 mb-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-1" style="color:var(--obe-red);"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="small mb-0">
                    <strong>Default sistem:</strong> Genap 01 Feb – 31 Jul, Ganjil 01 Agu – 31 Jan.
                    Saat tanggal hari ini melewati periode aktif, sistem akan membuat record semester baru sesuai default.
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-obe-red">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-sidebar-layout>
