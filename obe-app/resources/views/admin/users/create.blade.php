<x-sidebar-layout :title="'Tambah User'" :header="'Tambah ' . (request('role') ? ucfirst(request('role')) : 'User')">

    <div class="obe-card">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NIP / NIM <span class="text-danger">*</span></label>
                    <input type="text" name="identity" value="{{ old('identity') }}" required
                           class="form-control @error('identity') is-invalid @enderror" placeholder="Nomor identitas...">
                    @error('identity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama lengkap...">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if(request('role') !== 'mahasiswa')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Inisial Nama <small class="text-muted fw-normal">(opsional)</small></label>
                    <input type="text" name="initials" id="initials" value="{{ old('initials') }}" maxlength="20"
                           class="form-control text-uppercase @error('initials') is-invalid @enderror" style="font-family:monospace;" placeholder="Contoh: JDS">
                    <div class="form-text">Maksimal 20 karakter.</div>
                    @error('initials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="form-control @error('email') is-invalid @enderror" placeholder="contoh@email.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <input type="hidden" name="role" value="{{ request('role') }}">

                {{-- ── Jurusan ── --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jurusan</label>
                    <select name="jurusan_id" id="jurusan_id"
                            class="form-select @error('jurusan_id') is-invalid @enderror">
                        <option value="">— Pilih Jurusan —</option>
                        @foreach($jurusan as $j)
                            <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}{{ $j->kode ? ' (' . $j->kode . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('jurusan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- ── Program Studi (semua role kecuali admin/tendik/plp) ── --}}
                @php
                    $roleButuhProdi = in_array(request('role'), ['dosen','kaprodi','kajur','dekan','wakil_dekan','mahasiswa']);
                @endphp
                @if($roleButuhProdi)
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Program Studi
                        @if(in_array(request('role'), ['kaprodi','kajur','dosen','mahasiswa']))
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <select name="program_studi_id" id="program_studi_id"
                            class="form-select @error('program_studi_id') is-invalid @enderror"
                            {{ old('jurusan_id') ? '' : 'disabled' }}>
                        <option value="">— Pilih Program Studi —</option>
                    </select>
                    @error('program_studi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text" id="prodi-hint">Pilih jurusan terlebih dahulu.</div>
                </div>
                @endif

                @if(request('role') === 'mahasiswa')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Konsentrasi <span class="text-danger">*</span></label>
                    <select name="konsentrasi" required
                            class="form-select @error('konsentrasi') is-invalid @enderror">
                        <option value="" disabled {{ old('konsentrasi') ? '' : 'selected' }}>Pilih konsentrasi...</option>
                        <option value="RPL" {{ old('konsentrasi') === 'RPL' ? 'selected' : '' }}>RPL — Rekayasa Perangkat Lunak</option>
                        <option value="KCV" {{ old('konsentrasi') === 'KCV' ? 'selected' : '' }}>KCV — Komputasi Cerdas Visual</option>
                        <option value="KBJ" {{ old('konsentrasi') === 'KBJ' ? 'selected' : '' }}>KBJ — Komputasi Berbasis Jaringan</option>
                    </select>
                    @error('konsentrasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endif

                <div class="col-12">
                    <div class="alert alert-light border d-flex align-items-start gap-2 mb-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-1" style="color:var(--obe-red);"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="small mb-0">
                            <strong>Password default</strong> akan otomatis diatur sama dengan <strong>NIP/NIM</strong> yang dimasukkan.
                            Pengguna dapat mengubah password setelah login pertama.
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-obe-red">Simpan</button>
                <a href="{{ route('users.index', ['role' => request('role')]) }}" class="btn btn-obe-outline">Batal</a>
            </div>
        </form>
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
            const oldProdiId   = {{ old('program_studi_id') ? old('program_studi_id') : 'null' }};
            const apiBase      = '{{ url('/api/jurusan') }}';

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

            jurusanSel.addEventListener('change', () => loadProdi(jurusanSel.value));

            // Jika ada old value (form validation error), atau admin_jurusan (jurusan sudah terpilih otomatis), pre-load prodi
            const initJurusan = jurusanSel.value;
            if (initJurusan) {
                loadProdi(initJurusan, oldProdiId);
            } else if (jurusanSel.options.length === 2) {
                // Hanya satu pilihan jurusan (selain placeholder) → auto-select dan load prodi
                jurusanSel.selectedIndex = 1;
                loadProdi(jurusanSel.value, oldProdiId);
            }
        }
    </script>
</x-sidebar-layout>