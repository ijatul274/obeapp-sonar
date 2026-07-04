<x-sidebar-layout :title="'Kelola Akademik'" :header="'Kelola Akademik'">

    @include('partials._kelola_akademik_nav')

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted mb-0 small">Master data konsentrasi program studi.</p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalTambahKonsentrasi">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Konsentrasi
        </button>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="0,3"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">No</th>
                        <th class="text-center" style="width:160px;">Kode Konsentrasi</th>
                        <th>Nama Konsentrasi</th>
                        <th class="text-center" style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($konsentrasi as $i => $row)
                        <tr>
                            <td class="text-center text-muted small">{{ $i + 1 }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border" style="font-family:monospace;">{{ $row->kode }}</span>
                            </td>
                            <td class="fw-semibold">{{ $row->nama }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-obe-outline" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#modalEditKonsentrasi-{{ $row->id }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.konsentrasi.destroy', $row) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Hapus konsentrasi {{ $row->kode }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit per row --}}
                        <div class="modal fade" id="modalEditKonsentrasi-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.konsentrasi.update', $row) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Konsentrasi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                                                <input type="text" name="kode" value="{{ old('kode', $row->kode) }}" required maxlength="10"
                                                       class="form-control text-uppercase" style="font-family:monospace;">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                                                <input type="text" name="nama" value="{{ old('nama', $row->nama) }}" required maxlength="150"
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
                        <tr><td colspan="4" class="text-center text-muted py-5"><em>Belum ada konsentrasi.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambahKonsentrasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.konsentrasi.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Konsentrasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="kode" value="{{ old('kode') }}" required maxlength="10"
                                   class="form-control text-uppercase @error('kode') is-invalid @enderror"
                                   style="font-family:monospace;" placeholder="Contoh: RPL">
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="150"
                                   class="form-control @error('nama') is-invalid @enderror"
                                   placeholder="Contoh: Rekayasa Perangkat Lunak">
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                const m = document.getElementById('modalTambahKonsentrasi');
                if (m) new bootstrap.Modal(m).show();
            });
        @endif
    </script>
</x-sidebar-layout>
