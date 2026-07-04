<x-sidebar-layout :title="'Kelola Akun'" :header="'Kelola Akun'">

    @include('partials._kelola_akun_nav')

    @php
        $role = request('role');
        $konsentrasiList = $role === 'mahasiswa' ? \App\Models\Konsentrasi::orderBy('kode')->get() : collect();
        $konsentrasiByKode = $konsentrasiList->keyBy('kode');
        $prodiList = \App\Models\ProgramStudi::orderBy('nama_prodi')->get();
        $jurusanList = \App\Models\Jurusan::orderBy('nama_jurusan')->get();

        $needsProdi    = in_array($role, ['dosen', 'kaprodi', 'kajur', 'dekan', 'wakil_dekan', 'mahasiswa']);
        $needsJurusan  = in_array($role, ['admin_jurusan', 'kajur', 'kaprodi', 'dosen', 'mahasiswa', 'plp', 'dekan', 'wakil_dekan', 'tendik']);

        // Kelompokkan prodi per jurusan_id untuk filter dinamis di frontend
        $prodiByJurusan = $prodiList->groupBy('jurusan_id')->map(fn($list) =>
            $list->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama_prodi])->values()
        );
        $isPimpinanTab = $role === 'dekan'; // tab Pimpinan (dekan + wakil_dekan)

        $roleLabelMap = [
            'admin'         => 'Administrator',
            'admin_jurusan' => 'Admin Jurusan',
            'plp'           => 'PLP',
            'dekan'         => 'Pimpinan',
            'wakil_dekan'   => 'Pimpinan',
        ];
        $roleLabel = $roleLabelMap[$role] ?? ($role ? ucfirst($role) : 'User');

        $authUser      = auth()->user();
        $isAdminJurusan = $authUser && $authUser->role === 'admin_jurusan';
    @endphp

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <p class="text-muted mb-0 small">Manajemen akun pengguna sistem.</p>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah {{ $roleLabel }}
        </button>
    </div>

    <div class="obe-card p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 obe-dt"
                   data-no-sort="{{ $role === 'dosen' ? '0,7' : '0,4' }}"
                   data-page-length="50">
                <thead>
                    <tr>
                        <th class="text-center" style="width:50px;">No</th>
                        <th class="text-center" style="width:160px;">NIP / NIM</th>
                        <th>Nama Pengguna</th>
                        @if($role === 'mahasiswa')
                            <th class="text-center" style="width:160px;">Konsentrasi</th>
                        @elseif($isPimpinanTab)
                            <th class="text-center" style="width:220px;">Jabatan</th>
                        @elseif($role === 'dosen')
                            <th class="text-center" style="width:100px;">Inisial</th>
                            <th class="text-center" style="width:150px;">Jabatan / Status</th>
                        @elseif($role === 'kaprodi')
                            <th class="text-center" style="width:100px;">Inisial</th>
                            <th class="text-center" style="width:160px;">Prodi yang Dikepalai</th>
                        @else
                            <th class="text-center" style="width:100px;">Inisial</th>
                        @endif
                        @if(in_array($role, ['kajur','kaprodi','dosen','mahasiswa']))
                            <th style="width:180px;">Jurusan</th>
                            @if($role === 'kaprodi')
                                <th style="width:180px;">Prodi Asal (Dosen)</th>
                            @else
                                <th style="width:180px;">Program Studi</th>
                            @endif
                        @endif
                        <th class="text-center" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td class="text-center text-muted small">{{ $index + 1 }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border" style="font-family:monospace;">{{ $user->identity ?? '-' }}</span>
                            </td>
                            <td class="fw-semibold">{{ $user->name }}</td>

                            @if($role === 'mahasiswa')
                                <td class="text-center small">
                                    @php
                                        $kode = $user->profilMahasiswa->konsentrasi ?? null;
                                        $kons = $kode ? ($konsentrasiByKode[$kode] ?? null) : null;
                                    @endphp
                                    @if($kons)
                                        <span class="badge bg-light text-dark border" title="{{ $kons->nama }}">{{ $kons->kode }}</span>
                                        <div class="text-muted" style="font-size:.7rem;">{{ \Illuminate\Support\Str::limit($kons->nama, 24) }}</div>
                                    @elseif($kode)
                                        <span class="badge bg-light text-dark border">{{ $kode }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                            @elseif($isPimpinanTab)
                                <td class="text-center small">
                                    @php $p = $user->pengelola; @endphp
                                    @if($p)
                                        <span class="badge bg-light text-dark border">
                                            {{ $p->jabatan === 'dekan' ? 'Dekan' : 'Wakil Dekan' }}
                                        </span>
                                        @if($p->bidang)
                                            <div class="text-muted" style="font-size:.7rem;">
                                                Bidang {{ \Illuminate\Support\Str::limit($p->bidang, 32) }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                            @elseif($role === 'dosen')
                                {{-- Kolom Inisial --}}
                                <td class="text-center">
                                    @php
                                        $ini = $user->initials ? strtoupper($user->initials) : strtoupper(substr($user->name, 0, 1));
                                        $hasInitials = (bool) $user->initials;
                                        $bg = $hasInitials ? 'var(--obe-red)' : 'var(--obe-line-strong)';
                                    @endphp
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                          style="width:32px; height:32px; background:{{ $bg }}; font-size:.7rem;"
                                          title="{{ $user->initials ?? 'Belum ada inisial' }}">
                                        {{ substr($ini, 0, 3) }}
                                    </span>
                                </td>
                                {{-- Kolom Jabatan (dengan keterangan prodi untuk kaprodi) --}}
                                @php
                                    $jabatanSort = match($user->role) {
                                        'dekan'       => 1,
                                        'wakil_dekan' => 2,
                                        'kajur'       => 3,
                                        'kaprodi'     => 4,
                                        default       => 5,
                                    };
                                    // Nama prodi yang dikepalai (hanya relevan untuk kaprodi)
                                    $kaprodiProdi = $user->role === 'kaprodi'
                                        ? optional(optional($user->profilKaprodi)->programStudi)->nama_prodi
                                        : null;
                                @endphp
                                <td class="text-center" data-sort="{{ $jabatanSort }}">
                                    @if($user->role === 'kaprodi')
                                        {{-- Badge kaprodi: tampilkan nama prodi yang dikepalai --}}
                                        <span class="badge bg-warning text-dark">Kaprodi</span>
                                        @if($kaprodiProdi)
                                            <div class="text-muted" style="font-size:.68rem; margin-top:2px;">
                                                {{ \Illuminate\Support\Str::limit($kaprodiProdi, 28) }}
                                            </div>
                                        @else
                                            <div class="text-danger" style="font-size:.65rem; margin-top:2px;">
                                                (prodi belum diset)
                                            </div>
                                        @endif
                                    @elseif($user->role === 'kajur')
                                        <span class="badge bg-warning text-dark">Kajur</span>
                                    @elseif($user->role === 'dekan')
                                        <span class="badge bg-warning text-dark">Dekan</span>
                                    @elseif($user->role === 'wakil_dekan')
                                        <span class="badge bg-warning text-dark">Wadek</span>
                                    @else
                                        <span class="text-muted small">Dosen</span>
                                    @endif
                                </td>
                            @elseif($role === 'kaprodi')
                                {{-- Tab Kaprodi: Inisial + kolom "Prodi yang Dikepalai" --}}
                                @php
                                    $ini = $user->initials ? strtoupper($user->initials) : strtoupper(substr($user->name, 0, 1));
                                    $hasInitials = (bool) $user->initials;
                                    $bg = $hasInitials ? 'var(--obe-red)' : 'var(--obe-line-strong)';
                                    $prodiKepalai = optional(optional($user->profilKaprodi)->programStudi)->nama_prodi;
                                @endphp
                                <td class="text-center">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                          style="width:32px; height:32px; background:{{ $bg }}; font-size:.7rem;"
                                          title="{{ $user->initials ?? 'Belum ada inisial' }}">
                                        {{ substr($ini, 0, 3) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($prodiKepalai)
                                        <span class="badge bg-primary text-white" style="font-size:.72rem;">
                                            Kaprodi
                                        </span>
                                        <div class="fw-semibold small mt-1">{{ $prodiKepalai }}</div>
                                    @else
                                        <span class="badge bg-warning text-dark">Kaprodi</span>
                                        <div class="text-danger small mt-1">Prodi belum ditetapkan</div>
                                    @endif
                                </td>
                            @else
                                <td class="text-center">
                                    @php
                                        $ini = $user->initials ? strtoupper($user->initials) : strtoupper(substr($user->name, 0, 1));
                                        $hasInitials = (bool) $user->initials;
                                        $bg = $hasInitials ? 'var(--obe-red)' : 'var(--obe-line-strong)';
                                        $jabatanLabel = match($user->role) {
                                            'kajur'      => 'Kajur',
                                            'dekan'      => 'Dekan',
                                            'wakil_dekan'=> 'Wadek',
                                            default      => null,
                                        };
                                    @endphp
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                          style="width:32px; height:32px; background:{{ $bg }}; font-size:.7rem;"
                                          title="{{ $user->initials ?? 'Belum ada inisial' }}">
                                        {{ substr($ini, 0, 3) }}
                                    </span>
                                    @if($jabatanLabel)
                                        <div class="mt-1">
                                            <span class="badge bg-warning text-dark" style="font-size:.6rem;">
                                                {{ $jabatanLabel }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                            @endif

                            {{-- Kolom Jurusan & Prodi --}}
                            @if(in_array($role, ['kajur','kaprodi','dosen','mahasiswa']))
                                @php
                                    $jurusanNama = optional($user->jurusan)->nama_jurusan ?? null;
                                    $prodiNama   = null;
                                    if ($role === 'kaprodi') {
                                        // Prodi Asal = profil dosen (jabatan akademiknya), BUKAN prodi yang dikepalai
                                        $prodiNama = optional(optional($user->profilDosen)->programStudi)->nama_prodi
                                            ?? optional(optional($user->profilKaprodi)->programStudi)->nama_prodi;
                                    } elseif (in_array($role, ['dosen', 'kajur'])) {
                                        $prodiNama = optional(optional($user->profilDosen)->programStudi)->nama_prodi;
                                    } elseif ($role === 'mahasiswa') {
                                        $prodiNama = optional(optional($user->profilMahasiswa)->programStudi)->nama_prodi;
                                    }
                                @endphp
                                <td class="small">{{ $jurusanNama ?? '—' }}</td>
                                <td class="small">{{ $prodiNama ?? '—' }}</td>
                            @endif

                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    @php
                                        $prodiId = null;
                                        $prodiKepalaiId = null;
                                        if ($user->role === 'kaprodi') {
                                            // Prodi asal (dosen) — untuk kolom "Prodi Asal (Dosen)"
                                            $prodiId = optional($user->profilDosen ?? null)->program_studi_id;
                                            // Prodi yang dikepalai — untuk kolom "Prodi yang Dikepalai"
                                            $prodiKepalaiId = optional($user->profilKaprodi ?? null)->program_studi_id;
                                        } elseif (in_array($user->role, ['dosen', 'kajur'])) {
                                            $prodiId = optional($user->profilDosen ?? null)->program_studi_id;
                                        }
                                        $pengelola = $user->pengelola ?? null;
                                        $editPayload = [
                                            'id'                  => $user->id,
                                            'identity'            => $user->identity,
                                            'name'                => $user->name,
                                            'initials'            => $user->initials,
                                            'email'               => $user->email,
                                            'role'                => $user->role,
                                            'jurusan_id'          => $user->jurusan_id,
                                            'konsentrasi'         => optional($user->profilMahasiswa ?? null)->konsentrasi,
                                            'program_studi_id'    => $prodiId,
                                            'prodi_kepalai_id'    => $prodiKepalaiId,
                                            'jabatan'             => $user->isPimpinan() ? optional($pengelola)->jabatan : null,
                                            'bidang'              => $user->isPimpinan() ? optional($pengelola)->bidang  : null,
                                            'updateUrl'           => route('users.update', $user),
                                            'resetUrl'            => route('users.resetPassword', $user),
                                        ];
                                    @endphp
                                    <button type="button" class="btn btn-sm btn-obe-outline" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#modalEditUser"
                                            data-user="{{ json_encode($editPayload) }}"
                                            onclick="prepareEditUserModal(this)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus user {{ $user->name }}?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-obe-red" title="Hapus">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ in_array($role, ['kajur','kaprodi','mahasiswa']) ? 6 : ($role === 'dosen' ? 7 : 5) }}" class="dataTables_empty text-center text-muted py-5">
                                <em>Belum ada data {{ strtolower($roleLabel) }}.</em>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================================================================
         Modal Tambah User
    ================================================================ --}}
    <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah {{ $roleLabel }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- NIP / NIM --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIP / NIM <span class="text-danger">*</span></label>
                                <input type="text" name="identity" value="{{ old('identity') }}" required
                                       class="form-control @error('identity') is-invalid @enderror" placeholder="Nomor identitas...">
                                @error('identity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Nama Lengkap --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama lengkap...">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Inisial (bukan mahasiswa) --}}
                            @if($role !== 'mahasiswa')
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Inisial Nama <small class="text-muted fw-normal">(opsional)</small></label>
                                <input type="text" name="initials" id="initialsModal" value="{{ old('initials') }}" maxlength="20"
                                       class="form-control text-uppercase @error('initials') is-invalid @enderror"
                                       style="font-family:monospace;" placeholder="Contoh: JDS">
                                <div class="form-text">Maksimal 20 karakter.</div>
                                @error('initials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endif

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                       class="form-control @error('email') is-invalid @enderror" placeholder="contoh@email.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <input type="hidden" name="role" value="{{ $role }}">

                            {{-- Konsentrasi (mahasiswa) --}}
                            @if($role === 'mahasiswa')
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konsentrasi <span class="text-danger">*</span></label>
                                <select name="konsentrasi" required
                                        class="form-select @error('konsentrasi') is-invalid @enderror">
                                    <option value="" disabled {{ old('konsentrasi') ? '' : 'selected' }}>Pilih konsentrasi...</option>
                                    @foreach($konsentrasiList as $k)
                                        <option value="{{ $k->kode }}" {{ old('konsentrasi') === $k->kode ? 'selected' : '' }}>
                                            {{ $k->kode }} — {{ $k->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('konsentrasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endif

                            {{-- Jabatan + Bidang (pimpinan) --}}
                            @if($isPimpinanTab)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                                <select name="jabatan" id="jabatanTambah" required
                                        class="form-select @error('jabatan') is-invalid @enderror"
                                        onchange="toggleBidangTambah(this.value)">
                                    <option value="" disabled {{ old('jabatan') ? '' : 'selected' }}>Pilih jabatan...</option>
                                    <option value="dekan"       {{ old('jabatan') === 'dekan'       ? 'selected' : '' }}>Dekan</option>
                                    <option value="wakil_dekan" {{ old('jabatan') === 'wakil_dekan' ? 'selected' : '' }}>Wakil Dekan</option>
                                </select>
                                @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 {{ old('jabatan') === 'wakil_dekan' ? '' : 'd-none' }}" id="bidangWrapTambah">
                                <label class="form-label fw-semibold">Bidang <span class="text-danger">*</span></label>
                                <select name="bidang" id="bidangTambah"
                                        class="form-select @error('bidang') is-invalid @enderror"
                                        {{ old('jabatan') === 'wakil_dekan' ? 'required' : '' }}>
                                    <option value="" disabled {{ old('bidang') ? '' : 'selected' }}>Pilih bidang...</option>
                                    <option value="Akademik"
                                            {{ old('bidang') === 'Akademik' ? 'selected' : '' }}>
                                        Wakil Dekan Bidang Akademik
                                    </option>
                                    <option value="Keuangan dan Umum"
                                            {{ old('bidang') === 'Keuangan dan Umum' ? 'selected' : '' }}>
                                        Wakil Dekan Bidang Keuangan dan Umum
                                    </option>
                                    <option value="Kemahasiswaan, Alumni, dan Kerja Sama"
                                            {{ old('bidang') === 'Kemahasiswaan, Alumni, dan Kerja Sama' ? 'selected' : '' }}>
                                        Wakil Dekan Bidang Kemahasiswaan, Alumni, dan Kerja Sama
                                    </option>
                                </select>
                                @error('bidang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endif

                            {{-- Jurusan (dipilih dulu, prodi akan difilter otomatis) --}}
                            @if($needsJurusan)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jurusan @if(!$isAdminJurusan)<span class="text-danger">*</span>@endif</label>
                                @if($isAdminJurusan)
                                    <input type="text" class="form-control" value="{{ optional(auth()->user()->jurusan)->nama_jurusan ?? '—' }}" readonly>
                                @else
                                    <select name="jurusan_id" id="tambahJurusan" {{ $needsJurusan ? 'required' : '' }}
                                            class="form-select @error('jurusan_id') is-invalid @enderror"
                                            onchange="filterProdiTambah(this.value)">
                                        <option value="">— Pilih Jurusan —</option>
                                        @foreach($jurusanList as $j)
                                            <option value="{{ $j->id }}" {{ (string) old('jurusan_id') === (string) $j->id ? 'selected' : '' }}>
                                                {{ $j->nama_jurusan }}@if($j->kode) ({{ $j->kode }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('jurusan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endif

                            {{-- Prodi yang Dikepalai (khusus kaprodi) --}}
                            @if($role === 'kaprodi')
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Prodi yang Dikepalai
                                    <span class="text-danger">*</span>
                                    <small class="text-muted fw-normal ms-1">— jabatan struktural</small>
                                </label>
                                <select name="prodi_kepalai_id" id="tambahProdiKepalai"
                                        class="form-select @error('prodi_kepalai_id') is-invalid @enderror"
                                        required>
                                    <option value="">— Pilih Prodi yang Dikepalai —</option>
                                    @foreach($prodiList as $p)
                                        <option value="{{ $p->id }}"
                                                data-jurusan="{{ $p->jurusan_id }}"
                                                {{ (string) old('prodi_kepalai_id') === (string) $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Program studi yang dipimpin oleh kaprodi ini.</div>
                                @error('prodi_kepalai_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endif

                            {{-- Program Studi Asal / Dosen (difilter berdasarkan jurusan yang dipilih) --}}
                            @if($needsProdi)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    @if($role === 'kaprodi')
                                        Prodi Asal <small class="text-muted fw-normal ms-1">— sebagai dosen</small>
                                    @else
                                        Program Studi
                                    @endif
                                </label>
                                <select name="program_studi_id" id="tambahProdi"
                                        class="form-select @error('program_studi_id') is-invalid @enderror">
                                    <option value="">— Pilih Program Studi —</option>
                                    @foreach($prodiList as $p)
                                        <option value="{{ $p->id }}"
                                                data-jurusan="{{ $p->jurusan_id }}"
                                                {{ (string) old('program_studi_id') === (string) $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_prodi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('program_studi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endif

                            {{-- Info password default --}}
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================
         Modal Edit User
    ================================================================ --}}
    <div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="formEditUser" action="">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit <span id="editRoleLabel">{{ $roleLabel }}</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIP / NIM <span class="text-danger">*</span></label>
                                <input type="text" name="identity" id="editIdentity" required class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="editName" required class="form-control">
                            </div>
                            <div class="col-md-6" id="editInitialsWrap">
                                <label class="form-label fw-semibold">Inisial Nama <small class="text-muted fw-normal">(opsional)</small></label>
                                <input type="text" name="initials" id="editInitials" maxlength="20"
                                       class="form-control text-uppercase" style="font-family:monospace;" placeholder="Contoh: JDS">
                                <div class="form-text">Maksimal 20 karakter.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="editEmail" required class="form-control">
                            </div>
                            <input type="hidden" name="role" id="editRole" value="">

                            {{-- Konsentrasi --}}
                            <div class="col-md-6 d-none" id="editKonsentrasiWrap">
                                <label class="form-label fw-semibold">Konsentrasi <span class="text-danger">*</span></label>
                                <select name="konsentrasi" id="editKonsentrasi" class="form-select">
                                    <option value="" disabled selected>Pilih konsentrasi...</option>
                                    @foreach(\App\Models\Konsentrasi::orderBy('kode')->get() as $k)
                                        <option value="{{ $k->kode }}">{{ $k->kode }} — {{ $k->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Jabatan (pimpinan) --}}
                            <div class="col-md-6 d-none" id="editJabatanWrap">
                                <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                                <select name="jabatan" id="editJabatan" class="form-select"
                                        onchange="toggleBidangEdit(this.value)">
                                    <option value="" disabled>Pilih jabatan...</option>
                                    <option value="dekan">Dekan</option>
                                    <option value="wakil_dekan">Wakil Dekan</option>
                                </select>
                            </div>

                            {{-- Bidang (wakil dekan) --}}
                            <div class="col-md-6 d-none" id="editBidangWrap">
                                <label class="form-label fw-semibold">Bidang <span class="text-danger">*</span></label>
                                <select name="bidang" id="editBidang" class="form-select">
                                    <option value="" disabled>Pilih bidang...</option>
                                    <option value="Akademik">Wakil Dekan Bidang Akademik</option>
                                    <option value="Keuangan dan Umum">Wakil Dekan Bidang Keuangan dan Umum</option>
                                    <option value="Kemahasiswaan, Alumni, dan Kerja Sama">Wakil Dekan Bidang Kemahasiswaan, Alumni, dan Kerja Sama</option>
                                </select>
                            </div>

                            {{-- Jurusan (dipilih dulu, prodi akan difilter otomatis) --}}
                            <div class="col-md-6 d-none" id="editJurusanWrap">
                                <label class="form-label fw-semibold">Jurusan</label>
                                @if($isAdminJurusan)
                                    <input type="text" class="form-control" value="{{ optional(auth()->user()->jurusan)->nama_jurusan ?? '—' }}" readonly>
                                @else
                                    <select name="jurusan_id" id="editJurusan" class="form-select"
                                            onchange="filterProdiEdit(this.value)">
                                        <option value="">— Pilih Jurusan —</option>
                                        @foreach($jurusanList as $j)
                                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}@if($j->kode) ({{ $j->kode }})@endif</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            {{-- Prodi yang Dikepalai (khusus kaprodi, dikontrol JS) --}}
                            <div class="col-md-6 d-none" id="editProdiKepalaiWrap">
                                <label class="form-label fw-semibold">
                                    Prodi yang Dikepalai
                                    <span class="text-danger">*</span>
                                    <small class="text-muted fw-normal ms-1">— jabatan struktural</small>
                                </label>
                                <select name="prodi_kepalai_id" id="editProdiKepalai" class="form-select">
                                    <option value="">— Pilih Prodi yang Dikepalai —</option>
                                    @foreach($prodiList as $p)
                                        <option value="{{ $p->id }}" data-jurusan="{{ $p->jurusan_id }}">{{ $p->nama_prodi }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Program studi yang dipimpin oleh kaprodi ini.</div>
                            </div>

                            {{-- Program Studi Asal (difilter berdasarkan jurusan yang dipilih) --}}
                            <div class="col-md-6 d-none" id="editProdiWrap">
                                <label class="form-label fw-semibold" id="editProdiLabel">Program Studi</label>
                                <select name="program_studi_id" id="editProdi" class="form-select">
                                    <option value="">— Pilih Program Studi —</option>
                                    @foreach($prodiList as $p)
                                        <option value="{{ $p->id }}" data-jurusan="{{ $p->jurusan_id }}">{{ $p->nama_prodi }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-warning me-auto" id="editResetPwBtn">
                            Reset Password
                        </button>
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red">Perbarui</button>
                    </div>
                </form>
                <form id="editResetPwForm" method="POST" action="" class="d-none">@csrf</form>
            </div>
        </div>
    </div>

    <script>
        // ── Auto uppercase inisial ──────────────────────────────────────────
        const ini = document.getElementById('initialsModal');
        if (ini) ini.addEventListener('input', () => {
            const p = ini.selectionStart;
            ini.value = ini.value.toUpperCase();
            ini.setSelectionRange(p, p);
        });

        const editIni = document.getElementById('editInitials');
        if (editIni) editIni.addEventListener('input', () => {
            const p = editIni.selectionStart;
            editIni.value = editIni.value.toUpperCase();
            editIni.setSelectionRange(p, p);
        });

        // ── Populate modal edit ─────────────────────────────────────────────
        function prepareEditUserModal(btn) {
            try {
                const u    = JSON.parse(btn.getAttribute('data-user'));
                const form = document.getElementById('formEditUser');
                form.setAttribute('action', u.updateUrl || '');

                document.getElementById('editIdentity').value = u.identity || '';
                document.getElementById('editName').value     = u.name     || '';
                document.getElementById('editInitials').value = u.initials || '';
                document.getElementById('editEmail').value    = u.email    || '';
                document.getElementById('editRole').value     = u.role     || '';

                // Label judul modal
                const roleLabelMap = {
                    admin: 'Administrator', admin_jurusan: 'Admin Jurusan',
                    plp: 'PLP', dekan: 'Pimpinan', wakil_dekan: 'Pimpinan'
                };
                document.getElementById('editRoleLabel').textContent =
                    roleLabelMap[u.role] ?? (u.role ? (u.role.charAt(0).toUpperCase() + u.role.slice(1)) : 'User');

                // Inisial — sembunyikan untuk mahasiswa
                const isMhs = u.role === 'mahasiswa';
                document.getElementById('editInitialsWrap').classList.toggle('d-none', isMhs);

                // Konsentrasi
                const kWrap = document.getElementById('editKonsentrasiWrap');
                kWrap.classList.toggle('d-none', !isMhs);
                const kSel = document.getElementById('editKonsentrasi');
                kSel.required = isMhs;
                kSel.value = u.konsentrasi || '';

                // Jabatan + Bidang (pimpinan)
                const isPimpinan = ['dekan', 'wakil_dekan'].includes(u.role);
                const jabWrap    = document.getElementById('editJabatanWrap');
                jabWrap.classList.toggle('d-none', !isPimpinan);
                const jabSel = document.getElementById('editJabatan');
                jabSel.required = isPimpinan;
                jabSel.value = u.jabatan || '';
                toggleBidangEdit(u.jabatan || '', u.bidang || '');

                // Jurusan (set dulu, lalu filter prodi)
                const needsJurusan = ['admin_jurusan','dosen','kaprodi','kajur','dekan','wakil_dekan','tendik','plp','mahasiswa'].includes(u.role);
                document.getElementById('editJurusanWrap').classList.toggle('d-none', !needsJurusan);
                const jSel = document.getElementById('editJurusan');
                if (jSel) jSel.value = u.jurusan_id != null ? String(u.jurusan_id) : '';

                // Program Studi (tampilkan dulu, filter berdasarkan jurusan, lalu set nilai)
                const needsProdi = ['dosen','kaprodi','kajur','dekan','wakil_dekan','mahasiswa'].includes(u.role);
                document.getElementById('editProdiWrap').classList.toggle('d-none', !needsProdi);
                filterProdiEdit(u.jurusan_id != null ? String(u.jurusan_id) : '');
                const pSel = document.getElementById('editProdi');
                pSel.value = u.program_studi_id != null ? String(u.program_studi_id) : '';

                // Ubah label "Program Studi" → "Prodi Asal (sebagai dosen)" untuk kaprodi
                const prodiLabel = document.getElementById('editProdiLabel');
                if (prodiLabel) {
                    prodiLabel.innerHTML = u.role === 'kaprodi'
                        ? 'Prodi Asal <small class="text-muted fw-normal ms-1">— sebagai dosen</small>'
                        : 'Program Studi';
                }

                // Prodi yang Dikepalai (khusus kaprodi)
                const isKaprodi = u.role === 'kaprodi';
                const kepalaiWrap = document.getElementById('editProdiKepalaiWrap');
                kepalaiWrap.classList.toggle('d-none', !isKaprodi);
                const kepalaiSel = document.getElementById('editProdiKepalai');
                kepalaiSel.required = isKaprodi;
                if (isKaprodi) {
                    filterProdiKepalaiEdit(u.jurusan_id != null ? String(u.jurusan_id) : '');
                    kepalaiSel.value = u.prodi_kepalai_id != null ? String(u.prodi_kepalai_id) : '';
                }

                // Reset password
                const rstForm = document.getElementById('editResetPwForm');
                rstForm.setAttribute('action', u.resetUrl || '');
                document.getElementById('editResetPwBtn').onclick = () => {
                    if (confirm('Reset password user ini ke NIP/NIM (' + (u.identity || '') + ')?')) {
                        rstForm.submit();
                    }
                };
            } catch (e) {
                console.error('prepareEditUserModal failed', e);
            }
        }

        // ── Helper: toggle dropdown Bidang di modal Tambah ─────────────────
        function toggleBidangTambah(jabatan) {
            const wrap = document.getElementById('bidangWrapTambah');
            const sel  = document.getElementById('bidangTambah');
            const show = jabatan === 'wakil_dekan';
            wrap.classList.toggle('d-none', !show);
            sel.required = show;
            if (!show) sel.value = '';
        }

        // ── Helper: toggle dropdown Bidang di modal Edit ────────────────────
        function toggleBidangEdit(jabatan, bidangVal = null) {
            const wrap = document.getElementById('editBidangWrap');
            const sel  = document.getElementById('editBidang');
            const show = jabatan === 'wakil_dekan';
            wrap.classList.toggle('d-none', !show);
            sel.required = show;
            if (bidangVal !== null) sel.value = bidangVal;
            if (!show) sel.value = '';
        }

        // ── Filter prodi berdasarkan jurusan (modal Tambah) ─────────────────
        function filterProdiTambah(jurusanId) {
            const prodiSel = document.getElementById('tambahProdi');
            if (!prodiSel) return;
            const currentVal = prodiSel.value;
            prodiSel.innerHTML = '<option value="">— Pilih Program Studi —</option>';
            const allOptions = prodiSel.getAttribute('data-all-options');
            // Gunakan semua option dari data atribut
            document.querySelectorAll('#tambahProdi-source option').forEach(opt => {
                if (!jurusanId || opt.dataset.jurusan === String(jurusanId)) {
                    const newOpt = opt.cloneNode(true);
                    if (newOpt.value === currentVal) newOpt.selected = true;
                    prodiSel.appendChild(newOpt);
                }
            });
        }

        // ── Filter prodi berdasarkan jurusan (modal Edit) ───────────────────
        function filterProdiEdit(jurusanId) {
            const prodiSel = document.getElementById('editProdi');
            if (!prodiSel) return;
            const currentVal = prodiSel.value;
            prodiSel.innerHTML = '<option value="">— Pilih Program Studi —</option>';
            document.querySelectorAll('#editProdi-source option').forEach(opt => {
                if (!jurusanId || opt.dataset.jurusan === String(jurusanId)) {
                    const newOpt = opt.cloneNode(true);
                    if (newOpt.value === currentVal) newOpt.selected = true;
                    prodiSel.appendChild(newOpt);
                }
            });
            // Sync filter ke dropdown Prodi yang Dikepalai juga
            filterProdiKepalaiEdit(jurusanId);
        }

        // ── Filter prodi yang dikepalai (modal Edit) ────────────────────────
        function filterProdiKepalaiEdit(jurusanId) {
            const sel = document.getElementById('editProdiKepalai');
            if (!sel) return;
            const currentVal = sel.value;
            sel.innerHTML = '<option value="">— Pilih Prodi yang Dikepalai —</option>';
            document.querySelectorAll('#editProdiKepalai-source option').forEach(opt => {
                if (!jurusanId || opt.dataset.jurusan === String(jurusanId)) {
                    const newOpt = opt.cloneNode(true);
                    if (newOpt.value === currentVal) newOpt.selected = true;
                    sel.appendChild(newOpt);
                }
            });
        }

        // ── Inisialisasi: sembunyikan sumber, restore filter saat ada old() ─
        document.addEventListener('DOMContentLoaded', () => {
            // Buat elemen sumber tersembunyi untuk clone option (modal Tambah)
            const tambahProdi = document.getElementById('tambahProdi');
            if (tambahProdi) {
                const src = document.createElement('select');
                src.id = 'tambahProdi-source';
                src.style.display = 'none';
                tambahProdi.querySelectorAll('option[data-jurusan]').forEach(o => src.appendChild(o.cloneNode(true)));
                document.body.appendChild(src);

                // Jika ada nilai jurusan lama (setelah validation error), filter ulang
                const tambahJurusan = document.getElementById('tambahJurusan');
                if (tambahJurusan && tambahJurusan.value) {
                    const savedProdi = tambahProdi.value;
                    filterProdiTambah(tambahJurusan.value);
                    tambahProdi.value = savedProdi;
                }
            }

            // Buat elemen sumber tersembunyi untuk modal Edit
            const editProdi = document.getElementById('editProdi');
            if (editProdi) {
                const src2 = document.createElement('select');
                src2.id = 'editProdi-source';
                src2.style.display = 'none';
                editProdi.querySelectorAll('option[data-jurusan]').forEach(o => src2.appendChild(o.cloneNode(true)));
                document.body.appendChild(src2);
            }

            // Buat elemen sumber tersembunyi untuk Prodi yang Dikepalai (modal Edit)
            const editProdiKepalai = document.getElementById('editProdiKepalai');
            if (editProdiKepalai) {
                const src3 = document.createElement('select');
                src3.id = 'editProdiKepalai-source';
                src3.style.display = 'none';
                editProdiKepalai.querySelectorAll('option[data-jurusan]').forEach(o => src3.appendChild(o.cloneNode(true)));
                document.body.appendChild(src3);
            }
        });

        // ── Buka modal tambah otomatis jika ada validation error ───────────
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                const m = document.getElementById('modalTambahUser');
                if (m) new bootstrap.Modal(m).show();
            });
        @endif
    </script>

</x-sidebar-layout>