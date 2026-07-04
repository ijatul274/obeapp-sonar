<x-sidebar-layout :title="'Pengelola'" :header="'Multi-Jabatan Dosen'">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">Kelola jabatan tambahan yang dipegang dosen. Satu dosen dapat memegang lebih dari satu jabatan.</p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addJabatanModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Jabatan
        </button>
    </div>

    <div class="obe-card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Dosen</th>
                        <th>Jabatan</th>
                        <th>Keterangan</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pengelola as $row)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $row->user?->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $row->user?->identity ?? '' }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $row->jabatan }}</span></td>
                        <td>{{ $row->keterangan ?? '—' }}</td>
                        <td class="small">
                            {{ $row->mulai_menjabat?->format('d M Y') ?? '—' }}
                            <span class="text-muted">s.d.</span>
                            {{ $row->selesai_menjabat?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            @if($row->aktif)
                                <span class="badge" style="background:#dcfce7; color:#15803d;">Aktif</span>
                            @else
                                <span class="badge bg-light text-muted border">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-obe-outline" data-bs-toggle="modal" data-bs-target="#editJabatanModal{{ $row->id }}">Edit</button>
                            <form action="{{ route('admin.pengelola.destroy', $row) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus jabatan ini?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-obe-red">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Edit --}}
                    <div class="modal fade" id="editJabatanModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('admin.pengelola.update', $row) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Jabatan — {{ $row->user?->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Jabatan</label>
                                            <select name="jabatan" class="form-select" required>
                                                @foreach($jabatanOptions as $opt)
                                                    <option value="{{ $opt }}" @selected($row->jabatan === $opt)>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" class="form-control" value="{{ $row->keterangan }}">
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label">Mulai</label>
                                                <input type="date" name="mulai_menjabat" class="form-control" value="{{ $row->mulai_menjabat?->format('Y-m-d') }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Selesai</label>
                                                <input type="date" name="selesai_menjabat" class="form-control" value="{{ $row->selesai_menjabat?->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif{{ $row->id }}" @checked($row->aktif)>
                                            <label class="form-check-label" for="aktif{{ $row->id }}">Aktif</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-light" data-bs-dismiss="modal" type="button">Batal</button>
                                        <button class="btn btn-obe-red">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada jabatan ditetapkan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="addJabatanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.pengelola.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Jabatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Dosen</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">— Pilih dosen —</option>
                                @foreach($dosen as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->identity }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <select name="jabatan" class="form-select" required>
                                @foreach($jabatanOptions as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Mulai</label>
                                <input type="date" name="mulai_menjabat" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Selesai</label>
                                <input type="date" name="selesai_menjabat" class="form-control">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktifNew" checked>
                            <label class="form-check-label" for="aktifNew">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal" type="button">Batal</button>
                        <button class="btn btn-obe-red">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-sidebar-layout>
