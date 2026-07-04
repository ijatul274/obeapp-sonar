<x-sidebar-layout :title="'Tambah Kelas'" :header="'Tambah Kelas Baru'">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted small mb-0">
            Periode aktif:
            <span class="fw-semibold" style="color:var(--obe-red);">{{ ucfirst($activePeriod['period_type']) }} {{ $activePeriod['academic_year'] }}</span>
        </p>
        <a href="{{ route('classrooms.index') }}" class="text-decoration-none small text-muted">&larr; Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="obe-card">
        <form action="{{ route('classrooms.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Kelas <span class="text-danger">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: TI-A 2025">
                    <div class="form-text">Gunakan nama deskriptif, misal: TI-A Angkatan 2025.</div>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
                    <input type="text" name="academic_year" required value="{{ old('academic_year', $selectedYear) }}"
                           class="form-control @error('academic_year') is-invalid @enderror" style="font-family:monospace;" placeholder="2024/2025">
                    <div class="form-text">Format: YYYY/YYYY.</div>
                    @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold d-block">Periode Semester <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="period_type" id="period-ganjil" value="ganjil"
                               {{ old('period_type', $selectedPeriod) === 'ganjil' ? 'checked' : '' }}>
                        <label for="period-ganjil" class="form-check-label">Ganjil <small class="text-muted">(Sem 1,3,5,7)</small></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="period_type" id="period-genap" value="genap"
                               {{ old('period_type', $selectedPeriod) === 'genap' ? 'checked' : '' }}>
                        <label for="period-genap" class="form-check-label">Genap <small class="text-muted">(Sem 2,4,6,8)</small></label>
                    </div>
                    @error('period_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mata Kuliah <span class="text-danger">*</span></label>
                    <select name="course_id" id="course_id" required class="form-select @error('course_id') is-invalid @enderror">
                        <option value="">— Pilih Mata Kuliah —</option>
                    </select>
                    <div class="form-text" id="mk-note">Pilihan disesuaikan dengan periode yang dipilih.</div>
                    @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Dosen Pengampu <span class="text-danger">*</span></label>
                    <select name="lecturer_id" required class="form-select @error('lecturer_id') is-invalid @enderror">
                        <option value="">— Pilih Dosen —</option>
                        @foreach($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('lecturer_id') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->name }}@if($dosen->identity) ({{ $dosen->identity }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('lecturer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top mt-4">
                <button type="submit" class="btn btn-obe-red">Simpan Kelas</button>
                <a href="{{ route('classrooms.index') }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
    </div>

    @php
        $allCoursesJs = $allCourses->map(fn($c) => [
            'id'=>$c->id, 'code'=>$c->code, 'name'=>$c->name, 'semester'=>$c->semester,
            'type'=>in_array($c->semester, [1,3,5,7]) ? 'ganjil' : 'genap',
        ])->values()->toArray();
    @endphp
    <script>
        const allCourses = @json($allCoursesJs);
        const oldCourseId = "{{ old('course_id') }}";
        function filterCourses(period) {
            const select = document.getElementById('course_id');
            const note = document.getElementById('mk-note');
            const filtered = allCourses.filter(c => c.type === period);
            select.innerHTML = '<option value="">— Pilih Mata Kuliah —</option>';
            filtered.forEach(c => {
                const o = document.createElement('option');
                o.value = c.id;
                o.textContent = `Sem ${c.semester} · ${c.code} — ${c.name}`;
                if (String(c.id) === oldCourseId) o.selected = true;
                select.appendChild(o);
            });
            note.textContent = `Menampilkan ${filtered.length} mata kuliah semester ${period}.`;
        }
        const checked = document.querySelector('input[name="period_type"]:checked');
        if (checked) filterCourses(checked.value);
        document.querySelectorAll('input[name="period_type"]').forEach(r => r.addEventListener('change', () => filterCourses(r.value)));
    </script>
</x-sidebar-layout>
