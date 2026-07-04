<x-sidebar-layout :title="'Profil Lulusan'" :header="'Profil Lulusan'">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">Kelola profil lulusan program studi.</p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Profil Lulusan
        </button>
    </div>

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="0,3"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th style="width:240px;">Nama Profil</th>
                        <th>Deskripsi</th>
                        <th class="text-center" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $i => $profile)
                        <tr>
                            <td class="text-center text-muted small">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $profile->name }}</td>
                            <td class="small">{{ $profile->description }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-obe-outline" data-bs-toggle="modal" data-bs-target="#editModal{{ $profile->id }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('graduate-profiles.destroy', $profile) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus profil {{ $profile->name }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-5"><em>Belum ada profil lulusan.</em></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('graduate-profiles.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Profil Lulusan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Profil <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
    @foreach($profiles as $profile)
        <div class="modal fade" id="editModal{{ $profile->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('graduate-profiles.update', $profile) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Profil: {{ $profile->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Profil <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $profile->name) }}" class="form-control" required>
                            </div>
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                                <textarea name="description" rows="3" class="form-control" required>{{ old('description', $profile->description) }}</textarea>
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
</x-sidebar-layout>
