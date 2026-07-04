<x-guest-layout :title="'Tim Pengembang — OBE'">

    {{-- Topbar publik --}}
    <header class="border-bottom bg-white" style="position:sticky; top:0; z-index:50;">
        <div class="container py-3 d-flex align-items-center justify-content-between">
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                <span class="d-inline-flex align-items-center justify-content-center fw-bold text-white"
                      style="width:36px; height:36px; background:var(--obe-red); border-radius:8px; font-size:.85rem;">OB</span>
                <div class="lh-sm">
                    <div class="fw-bold">OBE</div>
                    <small class="text-muted text-uppercase" style="font-size:.65rem; letter-spacing:.05em;">Teknik Informatika UNRI</small>
                </div>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-sm btn-obe-outline">&larr; Beranda</a>
                <a href="{{ route('login') }}" class="btn btn-sm btn-obe-red">Masuk</a>
            </div>
        </div>
    </header>

    <main class="py-5">
        <div class="container" style="max-width:920px;">
            <div class="text-center mb-5">
                <span class="badge mb-3 px-3 py-2" style="background:var(--obe-red-soft); color:var(--obe-red); font-size:.7rem; letter-spacing:.05em;">TIM PENGEMBANG</span>
                <h1 class="h3 fw-bold mb-2">Aplikasi OBE — Prodi Teknik Informatika</h1>
                <p class="text-muted">Daftar pembimbing dan pengembang aplikasi.</p>
            </div>

            {{-- Profil Pembimbing --}}
            <section class="mb-5">
                <div class="text-center text-white fw-bold py-2 mb-3 rounded" style="background:var(--obe-red); letter-spacing:.04em;">Profil Pembimbing</div>
                <div class="row g-4">
                    @php
                        $pembimbings = [
                            ['name'=>'Edi Susilo, S.Pd., M.Kom., M.Eng.', 'role'=>'Lecturer', 'expertise'=>'Software Engineering · Human-Computer Interaction · UI/UX', 'photo'=>null],
                            ['name'=>'Anhar, S.T., M.T., Ph.D.',         'role'=>'Lecturer', 'expertise'=>'Wireless Networking · Wireless Sensor Network',      'photo'=>null],
                        ];
                    @endphp
                    @foreach($pembimbings as $p)
                        <div class="col-md-6">
                            <div class="obe-card text-center p-4 h-100">
                                <div class="d-inline-flex align-items-center justify-content-center mx-auto mb-3 fw-bold text-white"
                                     style="width:96px; height:96px; background:var(--obe-ink); border-radius:50%; font-size:1.8rem; letter-spacing:.05em;">
                                    {{ strtoupper(substr($p['name'], 0, 1)) }}
                                </div>
                                <h3 class="h6 fw-bold mb-1">{{ $p['name'] }}</h3>
                                <div class="text-muted small mb-2">{{ $p['role'] }}</div>
                                <p class="text-muted small mb-2">{{ $p['expertise'] }}</p>
                                <div class="d-flex justify-content-center gap-3 small">
                                    <a href="#" class="text-decoration-none fw-semibold" style="color:var(--obe-red);">Website Pribadi</a>
                                    <span class="text-muted">·</span>
                                    <a href="#" class="text-decoration-none fw-semibold" style="color:var(--obe-red);">Sinta ID</a>
                                    <span class="text-muted">·</span>
                                    <a href="#" class="text-decoration-none fw-semibold" style="color:var(--obe-red);">PDDikti</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Aplikasi Kerja Praktek dan Skripsi --}}
            <section class="mb-5">
                <div class="text-center text-white fw-bold py-2 mb-3 rounded" style="background:var(--obe-red); letter-spacing:.04em;">Pengembang Aplikasi</div>
                @php
                    $devs = [
                        [
                            'name'  => 'Izzathul Mardiyah',
                            'nim'   => '2207XXXXXX',
                            'email' => 'izzathul@example.com',
                            'peran' => 'Merancang dan mengembangkan Aplikasi Pengelolaan Nilai Berbasis OBE untuk Prodi Teknik Informatika UNRI.',
                        ],
                    ];
                @endphp

                @foreach($devs as $d)
                    <div class="obe-card p-0 overflow-hidden mb-3">
                        <div class="row g-0">
                            <div class="col-md-3 d-flex align-items-center justify-content-center p-4" style="background:var(--obe-bg);">
                                <div class="d-inline-flex align-items-center justify-content-center fw-bold text-white"
                                     style="width:120px; height:120px; background:var(--obe-red); border-radius:14px; font-size:2rem;">
                                    {{ strtoupper(substr($d['name'], 0, 1)) }}
                                </div>
                            </div>
                            <div class="col-md-9 p-4">
                                <table class="table table-sm align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold text-muted" style="width:120px;">Nama Lengkap</td>
                                            <td>{{ $d['name'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">NIM</td>
                                            <td style="font-family:monospace;">{{ $d['nim'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Email</td>
                                            <td>{{ $d['email'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-muted">Peran</td>
                                            <td>{{ $d['peran'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </main>

    <footer class="border-top py-3 bg-white">
        <div class="container text-center small text-muted">
            Dikembangkan oleh Prodi Teknik Informatika UNRI
            (<a href="{{ route('tim-pengembang') }}" style="color:var(--obe-red); font-weight:600; text-decoration:none;">Izzathul Mardiyah</a>)
        </div>
    </footer>
</x-guest-layout>
