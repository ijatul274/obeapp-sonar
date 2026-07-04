<x-sidebar-layout :title="'Tambah Profil Lulusan'" :header="'Tambah Profil Lulusan'">
    <div class="obe-card">
        <form method="POST" action="{{ route('graduate-profiles.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Profil <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                <textarea name="description" rows="4" required class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="d-flex gap-2 pt-2 border-top">
                <button type="submit" class="btn btn-obe-red">Simpan</button>
                <a href="{{ route('graduate-profiles.index') }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
    </div>
</x-sidebar-layout>
