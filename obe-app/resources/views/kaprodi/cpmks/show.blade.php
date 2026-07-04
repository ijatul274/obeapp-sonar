<x-sidebar-layout :title="'Detail CPMK'" :header="'Detail CPMK ' . $cpmk->code">

    <div class="obe-card mb-3">
        <h2 class="obe-card__title mb-3">Informasi Umum</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Mata Kuliah</div>
                <div class="fw-semibold">{{ $cpmk->course->code }} — {{ $cpmk->course->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Dosen Pengampu</div>
                <div>
                    @if($cpmk->lecturer)
                        {{ $cpmk->lecturer->name }}
                    @else
                        <span class="text-muted fst-italic">Tidak ada dosen spesifik</span>
                    @endif
                </div>
            </div>
            <div class="col-12">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Kode CPMK</div>
                <div class="fw-bold h5" style="color:var(--obe-red);">{{ $cpmk->code }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Pernyataan</div>
                <div class="p-3 border rounded" style="background:var(--obe-bg);">{{ $cpmk->description }}</div>
            </div>
        </div>
    </div>

    <div class="obe-card mb-3">
        <h2 class="obe-card__title mb-3">Indikator Kinerja</h2>
        @if($cpmk->indicators->count() > 0)
            <ul class="list-group list-group-flush">
                @foreach($cpmk->indicators as $i => $ind)
                    <li class="list-group-item d-flex align-items-start gap-2">
                        <span class="badge rounded-pill" style="background:var(--obe-red); color:#fff;">{{ $i + 1 }}</span>
                        <span>{{ $ind->description }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted fst-italic mb-0">Belum ada indikator untuk CPMK ini.</p>
        @endif
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <a href="{{ route('cpmks.edit', $cpmk) }}" class="btn btn-obe-outline btn-sm">Edit</a>
            <form action="{{ route('cpmks.destroy', $cpmk) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus CPMK ini?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-obe-red btn-sm">Hapus</button>
            </form>
        </div>
        <a href="{{ route('courses.show', $cpmk->course_id) }}" class="btn btn-obe-outline btn-sm">&larr; Kembali ke Mata Kuliah</a>
    </div>

</x-sidebar-layout>
