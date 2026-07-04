<x-sidebar-layout :title="'Edit User'" :header="'Edit ' . ucfirst($user->role)">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted small mb-0">{{ ucfirst($user->role) }} &middot; <span style="font-family:monospace;">{{ $user->identity }}</span></p>
    </div>

    <div class="obe-card">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NIP / NIM <span class="text-danger">*</span></label>
                    <input type="text" name="identity" value="{{ old('identity', $user->identity) }}" required
                           class="form-control @error('identity') is-invalid @enderror">
                    @error('identity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if($user->role !== 'mahasiswa')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Inisial Nama <small class="text-muted fw-normal">(opsional)</small></label>
                    <input type="text" name="initials" id="initials" value="{{ old('initials', $user->initials) }}" maxlength="20"
                           class="form-control text-uppercase @error('initials') is-invalid @enderror" style="font-family:monospace;" placeholder="Contoh: JDS">
                    <div class="form-text">Maksimal 20 karakter.</div>
                    @error('initials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <input type="hidden" name="role" value="{{ $user->role }}">

                {{-- ── Jurusan ── --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jurusan</label>
                    <select name="jurusan_id" id="jurusan_id"
                            class="form-select @error('jurusan_id') is-invalid @enderror">
                        <option value="">— Pilih Jurusan —</option>
                        @foreach($jurusan as $j)
                            <option value="{{ $j->id }}"
                                {{ old('jurusan_id', $user->jurusan_id) == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}{{ $j->kode ? ' (' . $j->kode . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('jurusan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- ── Program Studi (semua role kecuali admin/tendik/plp) ── --}}
                @php
                    $roleButuhProdi = in_array($user->role, ['dosen','kaprodi','kajur','dekan','wakil_dekan','mahasiswa']);
                    $currentProdiId = old('program_studi_id', $currentProdiId);
                @endphp
                @if($roleButuhProdi)
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Program Studi
                        @if(in_array($user->role, ['kaprodi','kajur','dosen','mahasiswa']))
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <select name="program_studi_id" id="program_studi_id"
                            class="form-select @error('program_studi_id') is-invalid @enderror"
                            {{ $user->jurusan_id ? '' : 'disabled' }}>
                        <option value="">— Pilih Program Studi —</option>
                        @foreach($prodiList as $p)
                            <option value="{{ $p->id }}" {{ $currentProdiId == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_prodi }}{{ $p->kode ? ' (' . $p->kode . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('program_studi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text" id="prodi-hint">
                        {{ $user->jurusan_id ? '' : 'Pilih jurusan terlebih dahulu.' }}
                    </div>
                </div>
                @endif

                @if($user->role === 'mahasiswa')
                @php
                    $currKons = old('konsentrasi', optional($user->profilMahasiswa)->konsentrasi);
                    $konsentrasiList = \App\Models\Konsentrasi::orderBy('kode')->get();
                @endphp
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Konsentrasi <span class="text-danger">*</span></label>
                    <select name="konsentrasi" required
                            class="form-select @error('konsentrasi') is-invalid @enderror">
                        <option value="" disabled {{ $currKons ? '' : 'selected' }}>Pilih konsentrasi...</option>
                        @foreach($konsentrasiList as $k)
                            <option value="{{ $k->kode }}" {{ $currKons === $k->kode ? 'selected' : '' }}>
                                {{ $k->kode }} — {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('konsentrasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top align-items-center">
                <button type="submit" class="btn btn-obe-red">Perbarui</button>
                <a href="{{ route('users.index', ['role' => $user->role]) }}" class="btn btn-obe-outline">Batal</a>

                <button type="button" class="btn btn-outline-warning ms-auto d-inline-flex align-items-center gap-2"
                        onclick="if(confirm('Reset password user ini ke NIP/NIM ({{ $user->identity }})?')) document.getElementById('reset-pw-form').submit();">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Reset Password
                </button>
            </div>
        </form>
        <form id="reset-pw-form" action="{{ route('users.resetPassword', $user) }}" method="POST" class="d-none">@csrf</form>
    </div>

    <script>
        const ini = document.getElementById('initials');
        if (ini) ini.addEventListener('input', () => {
            const p = ini.selectionStart;
            ini.value = ini.value.toUpperCase();
            ini.setSelectionRange(p, p);
        });

        // ── Dropdown jurusan → prodi dinamis ───────────────────────────
        const jurusanSel = document.getElementById('jurusan_id');
        const prodiSel   = document.getElementById('program_studi_id');
        const prodiHint  = document.getElementById('prodi-hint');

        if (jurusanSel && prodiSel) {
            const currentProdiId = {{ $currentProdiId ? $currentProdiId : 'null' }};
            const apiBase        = '{{ url('/api/jurusan') }}';

            async function loadProdi(jurusanId, selectedId = null) {
                prodiSel.disabled = true;
                prodiSel.innerHTML = '<option value="">Memuat...</option>';
                if (!jurusanId) {
                    prodiSel.innerHTML = '<option value="">— Pilih Program Studi —</option>';
                    if (prodiHint) prodiHint.textContent = 'Pilih jurusan terlebih dahulu.';
                    return;
                }
                try {
                    const res  = await fetch(`${apiBase}/${jurusanId}/prodi`);
                    const data = await res.json();
                    prodiSel.innerHTML = '<option value="">— Pilih Program Studi —</option>';
                    if (data.length === 0) {
                        if (prodiHint) prodiHint.textContent = 'Tidak ada program studi untuk jurusan ini.';
                    } else {
                        data.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = p.nama_prodi + (p.kode ? ` (${p.kode})` : '');
                            if (selectedId && p.id == selectedId) opt.selected = true;
                            prodiSel.appendChild(opt);
                        });
                        if (prodiHint) prodiHint.textContent = '';
                    }
                    prodiSel.disabled = false;
                } catch (e) {
                    prodiSel.innerHTML = '<option value="">Gagal memuat data</option>';
                }
            }

            // Saat jurusan diganti, reset prodi
            jurusanSel.addEventListener('change', () => loadProdi(jurusanSel.value));

            // Pre-load prodi sesuai jurusan yang sudah tersimpan
            const initJurusan = jurusanSel.value;
            if (initJurusan) {
                loadProdi(initJurusan, currentProdiId);
            }
        }
    </script>
</x-sidebar-layout>
