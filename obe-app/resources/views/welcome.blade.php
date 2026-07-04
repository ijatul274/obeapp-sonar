<x-guest-layout :title="'Modul OBE — SIAT FT UNRI'">

    {{-- Topbar publik --}}
    <header class="border-bottom bg-white" style="position:sticky; top:0; z-index:50;">
        <div class="container py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center fw-bold text-white"
                      style="width:36px; height:36px; background:var(--obe-red); border-radius:8px; font-size:.8rem;">SI</span>
                <div class="lh-sm">
                    <div class="fw-bold">SIAT FT UNRI</div>
                    <small class="text-muted text-uppercase" style="font-size:.65rem; letter-spacing:.05em;">Sistem Informasi Akademik Terpadu</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('tim-pengembang') }}" class="btn btn-sm btn-obe-outline">Tim Pengembang</a>
                <a href="{{ route('login') }}" class="btn btn-sm btn-obe-red">Masuk</a>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="py-5" style="background:linear-gradient(180deg, var(--obe-bg) 0%, #fff 100%);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge mb-3 px-3 py-2" style="background:var(--obe-red-soft); color:var(--obe-red); font-size:.7rem; letter-spacing:.05em;">FAKULTAS TEKNIK · UNIVERSITAS RIAU</span>
                    <h1 class="display-5 fw-bold mb-3"><span style="color:var(--obe-red);">SIAT</span> FT UNRI</h1>
                    <p class="lead text-muted mb-2">
                        <strong>Sistem Informasi Akademik Terpadu</strong> Fakultas Teknik Universitas Riau —
                        platform terpadu yang menaungi berbagai layanan akademik fakultas dalam satu ekosistem.
                    </p>
                    <p class="text-muted mb-4" style="font-size:.95rem;">
                        Anda saat ini berada di salah satu modulnya: <strong style="color:var(--obe-red);">OBE</strong>
                        — pengelolaan nilai berbasis <em>Outcome-Based Education</em>.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('login') }}" class="btn btn-obe-red px-4 py-2">Masuk ke Aplikasi</a>
                        <a href="{{ route('tim-pengembang') }}" class="btn btn-obe-outline px-4 py-2">Tentang Tim Pengembang</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="obe-card p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center"
                                     style="width:44px; height:44px; background:var(--obe-red); color:#fff; border-radius:10px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7 4h10a2 2 0 012 2v14l-7-3-7 3V6a2 2 0 012-2z"/></svg>
                                </div>
                                <div>
                                    <div class="fw-bold">Capaian Pembelajaran Berjenjang</div>
                                    <small class="text-muted">CPL → CPMK → Indikator → Komponen Penilaian</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center"
                                     style="width:44px; height:44px; background:var(--obe-ink); color:#fff; border-radius:10px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <div class="fw-bold">Workflow Persetujuan CPMK</div>
                                    <small class="text-muted">Draft → Pending → Approved/Rejected dengan catatan revisi</small>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-inline-flex align-items-center justify-content-center"
                                     style="width:44px; height:44px; background:var(--obe-red-soft); color:var(--obe-red); border-radius:10px;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <div class="fw-bold">Transkrip Dual-Mode</div>
                                    <small class="text-muted">OBE (CPL Achievement) & Konvensional (SATU UNRI)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modul-Modul dalam SIAT FT UNRI --}}
    <section class="py-5 border-top" style="background:#fff;">
        <div class="container">
            <div class="text-center mb-4">
                <span class="badge px-3 py-2 mb-2" style="background:var(--obe-red-soft); color:var(--obe-red); font-size:.7rem; letter-spacing:.05em;">EKOSISTEM SIAT FT UNRI</span>
                <h2 class="h3 fw-bold mb-1">Modul yang Tersedia</h2>
                <p class="text-muted mb-0">SIAT FT UNRI dirancang sebagai payung bagi seluruh layanan akademik fakultas. Berikut modul-modulnya.</p>
            </div>

            <div class="row g-3">
                {{-- OBE — modul aktif --}}
                <div class="col-md-6 col-lg-4">
                    <div class="obe-card p-3 h-100 d-flex flex-column" style="border:2px solid var(--obe-red);">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div class="d-inline-flex align-items-center justify-content-center"
                                 style="width:42px; height:42px; background:var(--obe-red); color:#fff; border-radius:10px; font-weight:700; font-size:.85rem;">OBE</div>
                            <span class="badge" style="background:var(--obe-red); color:#fff; font-size:.65rem;">ANDA DI SINI</span>
                        </div>
                        <div class="fw-bold mb-1">OBE — Pengelolaan Nilai</div>
                        <small class="text-muted flex-grow-1">CPL, CPMK, Indikator, dan penilaian mahasiswa berbasis <em>Outcome-Based Education</em>.</small>
                        <div class="mt-2"><span class="badge bg-success-subtle text-success" style="font-size:.65rem;">● Aktif</span></div>
                    </div>
                </div>

                {{-- Akademik --}}
                <div class="col-md-6 col-lg-4">
                    <div class="obe-card p-3 h-100 d-flex flex-column">
                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                             style="width:42px; height:42px; background:var(--obe-ink); color:#fff; border-radius:10px; font-weight:700; font-size:.85rem;">AKD</div>
                        <div class="fw-bold mb-1">Akademik</div>
                        <small class="text-muted flex-grow-1">KRS, jadwal kuliah, daftar hadir, dan transkrip nilai konvensional terintegrasi SATU UNRI.</small>
                        <div class="mt-2"><span class="badge bg-light text-muted border" style="font-size:.65rem;">● Dalam Pengembangan</span></div>
                    </div>
                </div>

                {{-- KP --}}
                <div class="col-md-6 col-lg-4">
                    <div class="obe-card p-3 h-100 d-flex flex-column">
                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                             style="width:42px; height:42px; background:var(--obe-ink); color:#fff; border-radius:10px; font-weight:700; font-size:.85rem;">KP</div>
                        <div class="fw-bold mb-1">Kerja Praktik</div>
                        <small class="text-muted flex-grow-1">Pengajuan KP, monitoring pembimbing, seminar, dan unggah laporan akhir.</small>
                        <div class="mt-2"><span class="badge bg-light text-muted border" style="font-size:.65rem;">● Dalam Pengembangan</span></div>
                    </div>
                </div>

                {{-- TA/Skripsi --}}
                <div class="col-md-6 col-lg-4">
                    <div class="obe-card p-3 h-100 d-flex flex-column">
                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                             style="width:42px; height:42px; background:var(--obe-ink); color:#fff; border-radius:10px; font-weight:700; font-size:.85rem;">TA</div>
                        <div class="fw-bold mb-1">Tugas Akhir / Skripsi</div>
                        <small class="text-muted flex-grow-1">Pengajuan judul, bimbingan, sidang, dan repository tugas akhir mahasiswa.</small>
                        <div class="mt-2"><span class="badge bg-light text-muted border" style="font-size:.65rem;">● Dalam Pengembangan</span></div>
                    </div>
                </div>

                {{-- Pengumuman --}}
                <div class="col-md-6 col-lg-4">
                    <div class="obe-card p-3 h-100 d-flex flex-column">
                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                             style="width:42px; height:42px; background:var(--obe-ink); color:#fff; border-radius:10px; font-weight:700; font-size:.85rem;">PGM</div>
                        <div class="fw-bold mb-1">Pengumuman</div>
                        <small class="text-muted flex-grow-1">Surat usulan dan pengumuman terstruktur lintas prodi di lingkungan FT UNRI.</small>
                        <div class="mt-2"><span class="badge bg-light text-muted border" style="font-size:.65rem;">● Dalam Pengembangan</span></div>
                    </div>
                </div>

                {{-- Kepegawaian --}}
                <div class="col-md-6 col-lg-4">
                    <div class="obe-card p-3 h-100 d-flex flex-column">
                        <div class="d-inline-flex align-items-center justify-content-center mb-2"
                             style="width:42px; height:42px; background:var(--obe-ink); color:#fff; border-radius:10px; font-weight:700; font-size:.85rem;">PEG</div>
                        <div class="fw-bold mb-1">Kepegawaian</div>
                        <small class="text-muted flex-grow-1">Data dosen & tendik, jabatan, beban kerja, dan riwayat kepangkatan.</small>
                        <div class="mt-2"><span class="badge bg-light text-muted border" style="font-size:.65rem;">● Dalam Pengembangan</span></div>
                    </div>
                </div>
            </div>

            <p class="text-center text-muted small mt-4 mb-0 fst-italic">
                Modul lainnya akan ditambahkan secara bertahap mengikuti kebutuhan tata kelola Fakultas Teknik UNRI.
            </p>
        </div>
    </section>

    <footer class="border-top py-3 bg-white">
        <div class="container text-center small text-muted">
            Dikembangkan oleh Prodi Teknik Informatika UNRI
            (<a href="{{ route('tim-pengembang') }}" style="color:var(--obe-red); font-weight:600; text-decoration:none;">Izzathul Mardiyah</a>)
        </div>
    </footer>
</x-guest-layout>
