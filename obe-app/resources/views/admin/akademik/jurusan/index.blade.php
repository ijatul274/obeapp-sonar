<x-sidebar-layout :title="'Kelola Akademik'" :header="'Kelola Akademik'">

    @include('partials._kelola_akademik_nav')

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted mb-0 small">Master data jurusan.</p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalTambahJurusan">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Jurusan
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="0,4"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">No</th>
                        <th class="text-center" style="width:120px;">Kode</th>
                        <th>Nama Jurusan</th>
                        <th>Program Studi</th>
                        <th class="text-center" style="width:160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurusan as $i => $row)
                        <tr>
                            <td class="text-center text-muted small">{{ $i + 1 }}</td>
                            <td class="text-center">
                                @if($row->kode)
                                    <span class="badge bg-light text-dark border" style="font-family:monospace;">{{ $row->kode }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $row->nama_jurusan }}</td>
                            <td>
                                @forelse($row->prodi as $p)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $p->nama_prodi }}</span>
                                @empty
                                    <span class="text-muted small fst-italic">Belum ada prodi</span>
                                @endforelse
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    {{-- Tombol Kelola Prodi --}}
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Kelola Prodi"
                                            data-bs-toggle="modal" data-bs-target="#modalProdi-{{ $row->id }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    </button>
                                    {{-- Tombol Edit --}}
                                    <button type="button" class="btn btn-sm btn-obe-outline" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#modalEditJurusan-{{ $row->id }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('admin.jurusan.destroy', $row) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Hapus jurusan {{ $row->nama_jurusan }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Kelola Prodi --}}
                        <div class="modal fade" id="modalProdi-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.jurusan.assign-prodi', $row) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                                Kelola Prodi — {{ $row->nama_jurusan }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small mb-3">Centang program studi yang berada di bawah jurusan ini.</p>
                                            @php $assignedIds = $row->prodi->pluck('id')->toArray(); @endphp
                                            @forelse($semuaProdi as $p)
                                                @php
                                                    $isAssignedHere    = in_array($p->id, $assignedIds);
                                                    $isAssignedElsewhere = $p->jurusan_id && $p->jurusan_id !== $row->id;
                                                @endphp
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="prodi_ids[]"
                                                           value="{{ $p->id }}"
                                                           id="prodi-{{ $row->id }}-{{ $p->id }}"
                                                           {{ $isAssignedHere ? 'checked' : '' }}
                                                           {{ $isAssignedElsewhere ? 'disabled' : '' }}>
                                                    <label class="form-check-label d-flex align-items-center gap-2"
                                                           for="prodi-{{ $row->id }}-{{ $p->id }}">
                                                        {{ $p->nama_prodi }}
                                                        @if($p->kode)
                                                            <span class="badge bg-light text-dark border" style="font-family:monospace;font-size:0.7rem;">{{ $p->kode }}</span>
                                                        @endif
                                                        @if($isAssignedElsewhere)
                                                            <span class="text-muted small fst-italic">(sudah di jurusan lain)</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            @empty
                                                <p class="text-muted fst-italic small">Belum ada program studi. Tambahkan di menu <strong>Kelola Prodi</strong> terlebih dahulu.</p>
                                            @endforelse
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-obe-red">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Edit Jurusan --}}
                        <div class="modal fade" id="modalEditJurusan-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.jurusan.update', $row) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Jurusan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Kode</label>
                                                <input type="text" name="kode" value="{{ old('kode', $row->kode) }}" maxlength="20"
                                                       class="form-control text-uppercase" style="font-family:monospace;">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label fw-semibold">Nama Jurusan <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_jurusan" value="{{ old('nama_jurusan', $row->nama_jurusan) }}" required maxlength="150"
                                                       class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-obe-red">Perbarui</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="5" class="dataTables_empty text-center text-muted py-5"><em>Belum ada jurusan.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Jurusan --}}
    <div class="modal fade" id="modalTambahJurusan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.jurusan.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Jurusan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode</label>
                            <input type="text" name="kode" value="{{ old('kode') }}" maxlength="20"
                                   class="form-control text-uppercase @error('kode') is-invalid @enderror"
                                   style="font-family:monospace;" placeholder="Contoh: TI">
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Nama Jurusan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jurusan" value="{{ old('nama_jurusan') }}" required maxlength="150"
                                   class="form-control @error('nama_jurusan') is-invalid @enderror"
                                   placeholder="Contoh: Teknik Informatika">
                            @error('nama_jurusan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                const m = document.getElementById('modalTambahJurusan');
                if (m) new bootstrap.Modal(m).show();
            });
        @endif
    </script>
</x-sidebar-layout>
