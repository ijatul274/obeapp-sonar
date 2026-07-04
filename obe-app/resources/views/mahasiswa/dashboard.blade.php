<x-sidebar-layout :title="'Dashboard Mahasiswa'" :header="'Dashboard Mahasiswa'">

    <div class="obe-card mb-3">
        <h2 class="obe-card__title mb-2">Bergabung ke Kelas</h2>
        <p class="text-muted small mb-3">Masukkan kode enrollment yang diberikan oleh dosen/kaprodi untuk bergabung ke kelas.</p>

        <form action="{{ route('mahasiswa.enroll') }}" method="POST" class="d-flex flex-column flex-sm-row gap-2">
            @csrf
            <input type="text" name="enrollment_code" required maxlength="8"
                   placeholder="Masukkan kode 8 karakter"
                   class="form-control text-uppercase" style="font-family:monospace;">
            <button type="submit" class="btn btn-obe-red flex-shrink-0">Bergabung</button>
        </form>
    </div>

    <div class="obe-card">
        <h2 class="obe-card__title mb-1">Mata Kuliah yang Diikuti</h2>
        <p class="text-muted small mb-3">Kelas semester aktif. Kelas dari semester lalu dapat dilihat di <a href="{{ route('mahasiswa.riwayat') }}" style="color:var(--obe-red);">Riwayat Kelas</a>.</p>

        @forelse($classrooms as $c)
            <div class="border rounded p-3 mb-2" style="transition:.15s; --bs-border-color:var(--obe-line);">
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
                        <span class="badge" style="background:var(--obe-red-soft); color:var(--obe-red);">Terdaftar</span>
                        <a href="{{ route('mahasiswa.classrooms.show', $c) }}" class="btn btn-obe-red btn-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <p class="mb-1 fst-italic">Anda belum terdaftar di kelas manapun.</p>
                <small>Gunakan kode enrollment di atas untuk bergabung.</small>
            </div>
        @endforelse
    </div>
</x-sidebar-layout>