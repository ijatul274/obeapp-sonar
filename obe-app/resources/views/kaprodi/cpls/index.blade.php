<x-sidebar-layout :title="'Daftar CPL'" :header="'CPL'">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">Capaian Pembelajaran Lulusan program studi.</p>
        <div class="d-flex gap-2">
            <!-- <button type="button" class="btn btn-obe-outline btn-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#minTargetModal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Set Minimal Ketercapaian
            </button> -->
            <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addModal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah CPL
            </button>
        </div>
    </div>

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="3"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th class="text-center" style="width:120px;">Kode</th>
                        <th>Pernyataan Capaian Pembelajaran Lulusan</th>
                        <th class="text-center" style="width:140px;">Min. Ketercapaian</th>
                        <th class="text-center" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cpls as $cpl)
                        <tr>
                            <td class="text-center fw-bold">{{ $cpl->code }}</td>
                            <td>{{ $cpl->description }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ rtrim(rtrim(number_format((float)$cpl->min_target, 2, '.', ''), '0'), '.') }}%</span></td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-obe-outline" data-bs-toggle="modal" data-bs-target="#editModal{{ $cpl->id }}" title="Edit">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('cpls.destroy', $cpl) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus CPL {{ $cpl->code }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-5"><em>Belum ada data CPL.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('cpls.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah CPL</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode CPL <span class="text-danger">*</span></label>
                            <input type="text" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" placeholder="Contoh: CPL-01" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pernyataan CPL <span class="text-danger">*</span></label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold">Minimal Ketercapaian (%) <span class="text-danger">*</span></label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" name="min_target" value="{{ old('min_target', 70) }}"
                                       required min="0" max="100" step="0.01"
                                       class="form-control @error('min_target') is-invalid @enderror">
                                <span class="input-group-text">%</span>
                                @error('min_target')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">Nilai antara 0 – 100. Default: 60%</div>
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

    {{-- Modal Edit per item --}}
    @foreach($cpls as $cpl)
        <div class="modal fade" id="editModal{{ $cpl->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('cpls.update', $cpl) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit CPL: {{ $cpl->code }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kode CPL <span class="text-danger">*</span></label>
                                <input type="text" name="code" value="{{ old('code', $cpl->code) }}" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pernyataan CPL <span class="text-danger">*</span></label>
                                <textarea name="description" rows="3" class="form-control" required>{{ old('description', $cpl->description) }}</textarea>
                            </div>
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Minimal Ketercapaian (%) <span class="text-danger">*</span></label>
                                <div class="input-group" style="max-width: 200px;">
                                    <input type="number" name="min_target" value="{{ old('min_target', $cpl->min_target) }}"
                                           required min="0" max="100" step="0.01"
                                           class="form-control">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Nilai antara 0 – 100.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-obe-red">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Set Minimal Ketercapaian --}}
    <div class="modal fade" id="minTargetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('cpls.min-target.update') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Set Minimal Ketercapaian CPL</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Tetapkan ambang minimal ketercapaian (%) untuk tiap CPL. Nilai default: 60%.</p>
                        @if($cpls->isEmpty())
                            <div class="text-muted text-center py-3"><em>Belum ada data CPL.</em></div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:120px;">Kode</th>
                                            <th>Pernyataan</th>
                                            <th class="text-center" style="width:160px;">Minimal (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cpls as $cpl)
                                            <tr>
                                                <td class="fw-semibold">{{ $cpl->code }}</td>
                                                <td class="small">{{ \Illuminate\Support\Str::limit($cpl->description, 100) }}</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="targets[{{ $cpl->id }}]"
                                                               value="{{ (float)$cpl->min_target }}"
                                                               class="form-control text-end" required>
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red" {{ $cpls->isEmpty() ? 'disabled' : '' }}>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-sidebar-layout>