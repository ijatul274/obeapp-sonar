<x-sidebar-layout :title="'Edit Kelas'" :header="'Edit Kelas: ' . $classroom->name">

    <div class="d-flex justify-content-between align-items-center mb-3">
        @if($classroom->period_type)
            <span class="badge bg-light text-dark border text-uppercase" style="letter-spacing:.04em;">
                {{ $classroom->period_type }} · {{ $classroom->academic_year }}
            </span>
        @endif
        <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-sm btn-obe-outline">&larr; Detail Kelas</a>
    </div>

    <div class="obe-card mb-3">
        <h2 class="obe-card__title mb-3">Edit Informasi Kelas</h2>
        <form action="{{ route('classrooms.update', $classroom) }}" method="POST">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Kelas</label>
                    <input type="text" name="name" value="{{ old('name', $classroom->name) }}" required class="form-control @error('name') is-invalid @enderror">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Periode Semester</label>
                    <div class="form-control bg-light">
                        {{ ucfirst($classroom->period_type) }} — {{ $classroom->academic_year }}
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mata Kuliah</label>
                    <select name="course_id" required class="form-select @error('course_id') is-invalid @enderror">
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ $classroom->course_id == $c->id ? 'selected' : '' }}>
                                Sem {{ $c->semester }} · {{ $c->code }} — {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dosen Pengampu</label>
                    <select name="lecturer_id" required class="form-select @error('lecturer_id') is-invalid @enderror">
                        <option value="">— Pilih Dosen —</option>
                        @foreach($dosens as $d)
                            <option value="{{ $d->id }}" {{ $classroom->lecturer_id == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}{{ $d->identity ? ' (' . $d->identity . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('lecturer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 pt-3 border-top mt-4">
                <button type="submit" class="btn btn-obe-red">Simpan Perubahan</button>
                <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
    </div>

    {{-- ── Penugasan CPMK ke Dosen ── --}}
    @if($classroom->course && $classroom->course->cpmks->count() > 0)
    <div class="obe-card mb-3">
        <h2 class="obe-card__title mb-1">Penugasan CPMK ke Dosen</h2>
        <p class="text-muted small mb-3">Tentukan dosen yang mengajar setiap CPMK pada kelas ini. Kaprodi dapat menugaskan dirinya sendiri agar kelas muncul di tampilan Dosen.</p>

        <form action="{{ route('classrooms.update', $classroom) }}" method="POST">
            @csrf @method('PUT')
            {{-- Kirim ulang field wajib agar validasi tidak gagal --}}
            <input type="hidden" name="name" value="{{ $classroom->name }}">
            <input type="hidden" name="course_id" value="{{ $classroom->course_id }}">
            @if($classroom->lecturer_id)
            <input type="hidden" name="lecturer_id" value="{{ $classroom->lecturer_id }}">
            @endif

            <div class="row g-2 mb-3">
                @foreach($classroom->course->cpmks as $cpmk)
                @php
                    $assigned = $cpmkLecturerMap[(string)$cpmk->id] ?? '';
                @endphp
                <div class="col-12">
                    <div class="border rounded p-3" style="background:#f8fafc;">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge fw-bold" style="background:#dcfce7; color:#166534;">{{ $cpmk->code }}</span>
                            @if($cpmk->meeting_range)
                                <span class="badge" style="background:#fef3c7; color:#854d0e;">Pertemuan {{ $cpmk->meeting_range }}</span>
                            @endif
                        </div>
                        <label class="form-label small fw-semibold mb-1 text-truncate d-block">
                            {{ $cpmk->code }} — {{ Str::limit($cpmk->description, 80) }}
                        </label>
                        <select name="cpmk_lecturers[{{ $cpmk->id }}]" class="form-select form-select-sm">
                            <option value="">— Pilih Dosen —</option>
                            @foreach($dosens as $d)
                                <option value="{{ $d->id }}" {{ (string)$d->id === (string)$assigned ? 'selected' : '' }}>
                                    {{ $d->name }}{{ $d->identity ? ' (' . $d->identity . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-obe-red btn-sm">Simpan Penugasan CPMK</button>
        </form>
    </div>
    @endif

    @if($classroom->students->count() > 0)
        <div class="obe-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="obe-card__title mb-0">Kelola Mahasiswa</h2>
                <span class="badge bg-light text-dark border">{{ $classroom->students->count() }} orang</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classroom->students as $idx => $student)
                            <tr>
                                <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                <td class="fw-semibold" style="font-family:monospace;">{{ $student->identity }}</td>
                                <td>{{ $student->name }}</td>
                                <td class="text-end">
                                    <form action="{{ route('classrooms.unenroll', [$classroom, $student]) }}" method="POST" class="m-0" onsubmit="return confirm('Keluarkan mahasiswa ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red">Keluarkan</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-sidebar-layout>