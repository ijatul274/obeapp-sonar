<x-guest-layout :title="'Login — OBE'">
    @php
        // Sumber slide carousel: tabel `login_slides` (akan dibuat di Phase B4).
        // Selama tabel belum ada, fallback ke placeholder default.
        try {
            $slides = \App\Models\LoginSlide::active()->ordered()->get();
        } catch (\Throwable $e) {
            $slides = collect();
        }

        if ($slides->isEmpty()) {
            $slides = collect([
                (object)[
                    'image_url' => 'https://images.unsplash.com/photo-1562774053-701939374585?w=1200&q=70',
                    'title'     => 'Selamat Datang di Aplikasi OBE',
                    'caption'   => 'Sistem Pengelolaan Nilai Kurikulum Berbasis Outcome-Based Education',
                ],
                (object)[
                    'image_url' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1200&q=70',
                    'title'     => 'Capaian Pembelajaran Terstruktur',
                    'caption'   => 'CPL · CPMK · Indikator · Komponen Penilaian',
                ],
                (object)[
                    'image_url' => 'https://images.unsplash.com/photo-1571260899304-425eee4c7efc?w=1200&q=70',
                    'title'     => 'Prodi Teknik Informatika UNRI',
                    'caption'   => 'Fakultas Teknik · Universitas Riau',
                ],
            ]);
        }
    @endphp

    <div class="min-vh-100 d-flex" style="background:var(--obe-bg);">

        {{-- LEFT: Carousel --}}
        <div class="d-none d-lg-block col-lg-7 position-relative" style="overflow:hidden;">
            <div id="loginCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="10000">
                <div class="carousel-inner h-100" style="height:100vh;">
                    @foreach($slides as $i => $slide)
                        <div class="carousel-item h-100 {{ $i === 0 ? 'active' : '' }}" style="height:100vh;">
                            <div class="position-absolute top-0 start-0 w-100 h-100"
                                 style="background:url('{{ $slide->image_url }}') center/cover no-repeat;"></div>
                            <div class="position-absolute top-0 start-0 w-100 h-100"
                                 style="background:linear-gradient(180deg, rgba(17,24,39,.25) 0%, rgba(17,24,39,.7) 100%);"></div>
                            <div class="position-absolute bottom-0 start-0 w-100 p-5 text-white" style="z-index:2;">
                                <h2 class="fw-bold mb-2" style="text-shadow:0 2px 12px rgba(0,0,0,.4);">{{ $slide->title }}</h2>
                                <p class="mb-0 opacity-90" style="text-shadow:0 2px 8px rgba(0,0,0,.4);">{{ $slide->caption }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($slides->count() > 1)
                    <div class="carousel-indicators" style="bottom:1rem;">
                        @foreach($slides as $i => $slide)
                            <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="{{ $i }}"
                                    class="{{ $i === 0 ? 'active' : '' }}"
                                    style="background-color:var(--obe-red);" aria-label="Slide {{ $i+1 }}"></button>
                        @endforeach
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#loginCarousel" data-bs-slide="prev"
                            style="width:auto; padding:0 1rem; opacity:.85;">
                        <span class="d-inline-flex align-items-center justify-content-center"
                              style="width:44px; height:44px; border-radius:50%; background:rgba(0,0,0,.35); backdrop-filter:blur(4px);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </span>
                        <span class="visually-hidden">Sebelumnya</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#loginCarousel" data-bs-slide="next"
                            style="width:auto; padding:0 1rem; opacity:.85;">
                        <span class="d-inline-flex align-items-center justify-content-center"
                              style="width:44px; height:44px; border-radius:50%; background:rgba(0,0,0,.35); backdrop-filter:blur(4px);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </span>
                        <span class="visually-hidden">Berikutnya</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- RIGHT: Form --}}
        <div class="col-12 col-lg-5 d-flex align-items-center justify-content-center p-4 p-md-5" style="background:#fff;">
            <div style="width:100%; max-width:400px;">

                <div class="d-flex align-items-center gap-3 mb-5">
                    <span><img src="{{ asset('images/logo_login.png') }}" alt="Logo"
     style="width:48px; height:48px; border-radius:10px; object-fit:cover;"></span>
                    <div>
                        <h1 class="h5 fw-bold mb-1">{{ \App\Models\Setting::where('key', 'login_title')->value('value') ?? 'Aplikasi Pengelolaan Nilai OBE' }}</h1>
                        <p class="text-muted small mb-0">{{ \App\Models\Setting::where('key', 'login_description')->value('value') ?? 'Prodi Teknik Informatika UNRI' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="identity" class="form-label fw-semibold">NIP / NIM <span class="text-danger">*</span></label>
                        <input id="identity" name="identity" type="text" class="form-control @error('identity') is-invalid @enderror"
                               placeholder="Masukkan NIP atau NIM" autofocus autocomplete="off" required>
                        @error('identity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4" x-data="{ show: false }">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input id="password" name="password" :type="show ? 'text' : 'password'"
                                   class="form-control pe-5 @error('password') is-invalid @enderror"
                                   placeholder="Masukkan password" autocomplete="current-password" required>
                            <button type="button" @click="show = !show"
                                    class="position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent text-muted me-2"
                                    style="line-height:1;">
                                <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show" style="display:none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 104.2 4.2M9.5 5.1A10 10 0 0112 5c6.5 0 10 7 10 7a18 18 0 01-3 4.2M6.6 6.6A18 18 0 002 12s3.5 7 10 7c1.4 0 2.7-.3 3.9-.7"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-obe-red w-100 fw-semibold py-2">Masuk</button>
                </form>

                <div class="text-center mt-4 small text-muted">
                    Dikembangkan oleh
                    <a href="{{ route('tim-pengembang') }}" class="fw-semibold text-decoration-none" style="color:var(--obe-red);">
                        TIM PRODI TEKNIK INFORMATIKA
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
