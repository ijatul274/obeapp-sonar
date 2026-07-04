<x-sidebar-layout :title="'Edit Mata Kuliah'" :header="'Edit Mata Kuliah'">

    <div class="obe-card">
        <form method="POST" action="{{ route('courses.update', $course) }}">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Nama Mata Kuliah <span class="text-danger">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $course->name) }}"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Algoritma dan Pemrograman" autocomplete="off">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                    <input type="text" name="code" required value="{{ old('code', $course->code) }}"
                           class="form-control @error('code') is-invalid @enderror" style="font-family:monospace;" placeholder="TIF101" autocomplete="off">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">SKS <span class="text-danger">*</span></label>
                    <input type="number" name="sks" min="1" required value="{{ old('sks', $course->sks) }}"
                           class="form-control @error('sks') is-invalid @enderror">
                    @error('sks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                    <select name="semester" required class="form-select @error('semester') is-invalid @enderror">
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" {{ old('semester', $course->semester) == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                    @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">MK Prasyarat</label>
                    <select name="prerequisite_course_id" class="form-select @error('prerequisite_course_id') is-invalid @enderror">
                        <option value="">— Tidak ada —</option>
                        @foreach($courses as $prereq)
                            <option value="{{ $prereq->id }}" {{ old('prerequisite_course_id', $course->prerequisite_course_id) == $prereq->id ? 'selected' : '' }}>
                                {{ $prereq->code }} — {{ $prereq->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('prerequisite_course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top mt-4">
                <button type="submit" class="btn btn-obe-red">Perbarui</button>
                <a href="{{ route('courses.index') }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
    </div>

</x-sidebar-layout>
