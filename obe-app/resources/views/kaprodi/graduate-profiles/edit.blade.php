<x-sidebar-layout :title="'Edit Profil Lulusan'" :header="'Edit Profil Lulusan'">
    <div class="obe-card">
        <form method="POST" action="{{ route('graduate-profiles.update', $graduateProfile) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Profil <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $graduateProfile->name) }}" required class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
                <textarea name="description" rows="4" required class="form-control @error('description') is-invalid @enderror">{{ old('description', $graduateProfile->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="d-flex gap-2 pt-2 border-top">
                <button type="submit" class="btn btn-obe-red">Perbarui</button>
                <a href="{{ route('graduate-profiles.index') }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
    </div>
</x-sidebar-layout>
