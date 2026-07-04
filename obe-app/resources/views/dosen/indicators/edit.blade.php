<x-sidebar-layout :title="'Kelola Komponen Penilaian'" :header="'Kelola Komponen Penilaian'">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted small mb-0">CPMK: <span class="fw-semibold">{{ $indicator->cpmk->code }}</span></p>
        <a href="{{ route('dosen.courses.show', $indicator->cpmk->course_id) }}" class="text-decoration-none small text-muted">&larr; Kembali ke Detail MK</a>
    </div>

    <div class="obe-card mb-3">
        <div class="text-muted small text-uppercase fw-semibold mb-2" style="font-size:.7rem;">Indikator</div>
        <div class="p-3 border rounded" style="background:var(--obe-bg);">{{ $indicator->description }}</div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="obe-card p-0 overflow-hidden">
                <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom" style="background:var(--obe-bg);">
                    <h2 class="obe-card__title mb-0">Daftar Komponen</h2>
                    <span class="badge bg-light text-dark border">Total: {{ $indicator->assessments->sum('percentage') }}%</span>
                </div>
                @if($indicator->assessments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center" style="width:100px;">Bobot</th>
                                    <th class="text-end" style="width:90px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($indicator->assessments as $a)
                                    <tr>
                                        <td><a href="{{ route('assessments.scores.index', $a) }}" class="fw-semibold text-decoration-none" style="color:var(--obe-red);">{{ $a->name }}</a></td>
                                        <td class="small text-muted">{{ $a->description ?? '-' }}</td>
                                        <td class="text-center">
                                            {{ number_format($a->percentage, 2) }}%
                                            @if(!$a->is_auto)<small class="text-warning fw-semibold ms-1">(Manual)</small>@endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('assessments.destroy', $a) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus komponen ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-obe-red">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted fst-italic">Belum ada komponen penilaian.</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="obe-card">
                <h2 class="obe-card__title mb-3">Tambah Komponen</h2>
                <form action="{{ route('assessments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="indicator_id" value="{{ $indicator->id }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Komponen</label>
                        <input type="text" name="name" required placeholder="Tugas 1" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Deskripsi tugas/asesmen..." class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bobot (%)</label>
                        <input type="number" name="percentage" min="0" max="100" step="0.01" placeholder="Kosongkan = otomatis" class="form-control">
                        <div class="form-text">Bobot kosong dibagi rata ke komponen otomatis.</div>
                    </div>
                    <button type="submit" class="btn btn-obe-red w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</x-sidebar-layout>
