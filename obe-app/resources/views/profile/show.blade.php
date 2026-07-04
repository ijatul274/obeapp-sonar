<x-sidebar-layout :title="'Detail Akun'" :header="'Detail Akun'">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0">Profil Pengguna</h2>
        <button type="button" class="btn btn-obe-red btn-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Ganti Password
        </button>
    </div>

    <div class="obe-card">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Nama Lengkap</div>
                <div class="fw-semibold">{{ $user->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">NIP / NIM</div>
                <div class="fw-semibold">{{ $user->identity }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Email</div>
                <div class="fw-semibold">{{ $user->email }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Peran</div>
                <span class="badge text-uppercase" style="background:var(--obe-red-soft); color:var(--obe-red); padding:.4rem .65rem; font-size:.7rem;">{{ $user->role }}</span>
            </div>
            <div class="col-md-6">
                <div class="text-muted small text-uppercase fw-semibold mb-1" style="letter-spacing:.05em;">Terdaftar Sejak</div>
                <div class="fw-semibold">{{ $user->created_at->format('d F Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Modal Ganti Password --}}
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Ganti Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Saat Ini</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" name="password" required minlength="8">
                            <div class="form-text">Minimal 8 karakter.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="password_confirmation" required minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-obe-outline" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-obe-red">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-sidebar-layout>
