<x-sidebar-layout :title="'Kelola Akademik'" :header="'Kelola Akademik'">

    @include('partials._kelola_akademik_nav')

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted mb-0 small">Master data program studi.</p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalTambahProdi">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Program Studi
        </button>
    </div>

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
                        <th class="text-center" style="width:140px;">Kode</th>
                        <th>Nama Program Studi</th>
                        <th>Jurusan</th>
                        <th class="text-center" style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prodi as $i => $row)
                        <tr>
                            <td class="text-center text-muted small">{{ $i + 1 }}</td>
                            <td class="text-center">
                                @if($row->kode)
                                    <span class="badge bg-light text-dark border" style="font-family:monospace;">{{ $row->kode }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $row->nama_prodi }}</td>
                            <td>
                                @if($row->jurusan)
                                    <span class="badge bg-light text-dark border">{{ $row->jurusan->nama_jurusan }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-obe-outline" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#modalEditProdi-{{ $row->id }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('admin.prodi.destroy', $row) }}" method="POST" class="m-0"
                                          onsubmit="return confirm('Hapus prodi {{ $row->nama_prodi }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div class="modal fade" id="modalEditProdi-{{ $row->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.prodi.update', $row) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Program Studi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Kode</label>
                                                <input type="text" name="kode" value="{{ old('kode', $row->kode) }}" maxlength="20"
                                                       class="form-control text-uppercase" style="font-family:monospace;">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Program Studi <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_prodi" value="{{ old('nama_prodi', $row->nama_prodi) }}" required maxlength="150"
                                                       class="form-control">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label fw-semibold">Jurusan</label>
                                                <select name="jurusan_id" class="form-select">
                                                    <option value="">— Pilih Jurusan —</option>
                                                    @foreach($jurusan as $j)
                                                        <option value="{{ $j->id }}"
                                                            {{ old('jurusan_id', $row->jurusan_id) == $j->id ? 'selected' : '' }}>
                                                            {{ $j->nama_jurusan }}
                                                            @if($j->kode) ({{ $j->kode }}) @endif
                                                        </option>
                                                    @endforeach
                                                </select>
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
                        <tr><td colspan="5" class="dataTables_empty text-center text-muted py-5"><em>Belum ada program studi.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambahProdi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.prodi.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Program Studi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode</label>
                            <input type="text" name="kode" value="{{ old('kode') }}" maxlength="20"
                                   class="form-control text-uppercase @error('kode') is-invalid @enderror"
                                   style="font-family:monospace;" placeholder="Contoh: PSTI">
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="nama_prodi" value="{{ old('nama_prodi') }}" required maxlength="150"
                                   class="form-control @error('nama_prodi') is-invalid @enderror"
                                   placeholder="Contoh: Teknik Informatika">
                            @error('nama_prodi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Jurusan</label>
                            <select name="jurusan_id" class="form-select @error('jurusan_id') is-invalid @enderror">
                                <option value="">— Pilih Jurusan —</option>
                                @foreach($jurusan as $j)
                                    <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                        {{ $j->nama_jurusan }}
                                        @if($j->kode) ({{ $j->kode }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('jurusan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                const m = document.getElementById('modalTambahProdi');
                if (m) new bootstrap.Modal(m).show();
            });
        @endif
    </script>
</x-sidebar-layout>
