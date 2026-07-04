<x-sidebar-layout :title="'Riwayat Kelas'" :header="'Riwayat Kelas'">

    <div class="obe-card">
        <h2 class="obe-card__title mb-1">Riwayat Kelas</h2>
        <p class="text-muted small mb-3">Kelas dari semester sebelumnya yang telah diarsipkan. Nilai tetap tersimpan dan dapat dilihat di <a href="{{ route('mahasiswa.transkrip') }}" style="color:var(--obe-red);">Hasil Studi</a>.</p>

        @forelse($classrooms as $c)
            <div class="border rounded p-3 mb-2" style="--bs-border-color:var(--obe-line);">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="flex-grow-1">
                        <h4 class="h6 fw-bold mb-1">{{ $c->course->name }}</h4>
                        <p class="text-muted small mb-1">{{ $c->course->code }} — {{ $c->name }}</p>
                        <div class="d-flex flex-wrap gap-3 small text-muted">
                            <span><strong>SKS:</strong> {{ $c->course->sks }}</span>
                            <span><strong>Semester:</strong> {{ $c->semester }}</span>
                            <span><strong>{{ ucfirst($c->period_type) }}</strong> {{ $c->academic_year }}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0">
                        <span class="badge" style="background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb;">Diarsipkan</span>
                        <a href="{{ route('mahasiswa.classrooms.show', $c) }}" class="btn btn-obe-outline btn-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     class="mb-2 d-block mx-auto" style="opacity:.3">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mb-1 fst-italic">Belum ada kelas yang diarsipkan.</p>
                <small>Kelas aktif Anda dapat dilihat di <a href="{{ route('mahasiswa.dashboard') }}" style="color:var(--obe-red);">Daftar Kelas</a>.</small>
            </div>
        @endforelse
    </div>

</x-sidebar-layout>