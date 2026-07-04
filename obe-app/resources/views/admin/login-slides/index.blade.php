<x-sidebar-layout :title="'Carousel Login'" :header="'Carousel Halaman Login'">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted small mb-0">Kelola slide yang muncul di sisi kiri halaman login.</p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSlideModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Slide
        </button>
    </div>

    <div class="row g-3">
        @forelse($slides as $slide)
            <div class="col-md-6 col-xl-4">
                <div class="obe-card p-0 overflow-hidden h-100 d-flex flex-column">
                    <div style="aspect-ratio:16/9; background:url('{{ $slide->image_url }}') center/cover no-repeat var(--obe-bg);"></div>
                    <div class="p-3 flex-grow-1 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <h3 class="h6 fw-bold mb-0">{{ $slide->title ?? '— Tanpa judul —' }}</h3>
                            @if($slide->is_active)
                                <span class="badge" style="background:#dcfce7; color:#15803d;">Aktif</span>
                            @else
                                <span class="badge bg-light text-muted border">Nonaktif</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-2 flex-grow-1">{{ $slide->caption ?? '—' }}</p>
                        <div class="d-flex align-items-center gap-2 text-muted small mb-3">
                            <span>Urutan: <strong>{{ $slide->sort_order }}</strong></span>
                        </div>
                        <div class="d-flex gap-2 mt-auto">
                            <button type="button" class="btn btn-sm btn-obe-outline flex-grow-1" data-bs-toggle="modal" data-bs-target="#editSlideModal{{ $slide->id }}">Edit</button>
                            <form action="{{ route('admin.login-slides.destroy', $slide) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus slide ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-obe-red">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Edit per slide --}}
            <div class="modal fade" id="editSlideModal{{ $slide->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="{{ route('admin.login-slides.update', $slide) }}" method="POST" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Edit Slide #{{ $slide->id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Gambar saat ini</label>
                                    <div class="border rounded" style="aspect-ratio:16/9; background:url('{{ $slide->image_url }}') center/cover no-repeat var(--obe-bg);"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ganti Gambar <small class="text-muted fw-normal">(opsional, max 4MB)</small></label>
                                    <input type="file" name="image" accept="image/*" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Judul</label>
                                    <input type="text" name="title" value="{{ $slide->title }}" maxlength="255" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Caption</label>
                                    <textarea name="caption" rows="2" maxlength="500" class="form-control">{{ $slide->caption }}</textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Urutan</label>
                                        <input type="number" name="sort_order" min="0" value="{{ $slide->sort_order }}" class="form-control">
                                    </div>
                                    <div class="col-6 d-flex align-items-end">
                                        <div class="form-check">
                                            <input type="hidden" name="is_active" value="0">
                                            <input class="form-check-input" type="checkbox" id="active{{ $slide->id }}" name="is_active" value="1" {{ $slide->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active{{ $slide->id }}">Aktifkan slide</label>
                                        </div>
                                    </div>
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
        @empty
            <div class="col-12">
                <div class="obe-card text-center py-5 text-muted">
                    <p class="fst-italic mb-1">Belum ada slide.</p>
                    <small>Selama belum ada, halaman login menampilkan placeholder default.</small>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="addSlideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.login-slides.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Tambah Slide</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Gambar <span class="text-danger">*</span></label>
                            <input type="file" name="image" accept="image/*" required class="form-control">
                            <div class="form-text">Disarankan rasio 16:9 atau landscape, maksimal 4MB.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul</label>
                            <input type="text" name="title" maxlength="255" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Caption</label>
                            <textarea name="caption" rows="2" maxlength="500" class="form-control"></textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Urutan</label>
                                <input type="number" name="sort_order" min="0" value="0" class="form-control">
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="addActive" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="addActive">Aktifkan slide</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red">Simpan Slide</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-sidebar-layout>
