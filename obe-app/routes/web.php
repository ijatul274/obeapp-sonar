<?php

use Illuminate\Support\Facades\Route;

// Landing publik dinonaktifkan sementara — '/' redirect langsung ke login.
Route::get('/', fn() => redirect()->route('login'))->name('home');
Route::get('/tim-pengembang', fn() => view('tim-pengembang'))->name('tim-pengembang');

Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');

    // User Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Admin Routes
    Route::get('/admin/obe', fn() => view('admin.obe.index'))->name('admin.obe.index');
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
    // "Kelola Akun" — sidebar entry redirect ke tab pertama; nav horizontal di top page menangani switching.
    Route::get('/admin/kelola-akun', function () {
        $user = auth()->user();
    
        // Mode admin prodi: langsung ke tab mahasiswa
        if ($user && $user->role === 'admin_jurusan' && session('role_mode') === 'admin_prodi') {
            $defaultRole = 'mahasiswa';
        } elseif ($user && $user->role === 'admin_jurusan') {
            // Admin jurusan mode normal: landing ke tab Kajur (bukan Administrator)
            $defaultRole = 'kajur';
        } else {
            $defaultRole = 'admin';
        }
    
        return redirect()->route('users.index', ['role' => $defaultRole]);
    })->name('admin.kelola-akun.index');

    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::post('users/{user}/reset-password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Admin Settings Route
    Route::get('/admin/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('/admin/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Admin — Carousel Login
    Route::get('/admin/login-slides',                 [App\Http\Controllers\LoginSlideController::class, 'index'])->name('admin.login-slides.index');
    Route::post('/admin/login-slides',                [App\Http\Controllers\LoginSlideController::class, 'store'])->name('admin.login-slides.store');
    Route::put('/admin/login-slides/{loginSlide}',    [App\Http\Controllers\LoginSlideController::class, 'update'])->name('admin.login-slides.update');
    Route::delete('/admin/login-slides/{loginSlide}', [App\Http\Controllers\LoginSlideController::class, 'destroy'])->name('admin.login-slides.destroy');

    // Admin — Kelola Akademik (Semester & Konsentrasi)
    Route::get('/admin/akademik', fn() => redirect()->route('admin.semester.index'))->name('admin.akademik.index');
    Route::get('/admin/akademik/semester',                [App\Http\Controllers\SemesterController::class, 'index'])->name('admin.semester.index');
    Route::put('/admin/akademik/semester/{semester}',     [App\Http\Controllers\SemesterController::class, 'update'])->name('admin.semester.update');

    Route::get('/admin/akademik/konsentrasi',                    [App\Http\Controllers\KonsentrasiController::class, 'index'])->name('admin.konsentrasi.index');
    Route::post('/admin/akademik/konsentrasi',                   [App\Http\Controllers\KonsentrasiController::class, 'store'])->name('admin.konsentrasi.store');
    Route::put('/admin/akademik/konsentrasi/{konsentrasi}',      [App\Http\Controllers\KonsentrasiController::class, 'update'])->name('admin.konsentrasi.update');
    Route::delete('/admin/akademik/konsentrasi/{konsentrasi}',   [App\Http\Controllers\KonsentrasiController::class, 'destroy'])->name('admin.konsentrasi.destroy');

    // Admin — Kelola Akademik (Jurusan & Prodi) — superadmin only
    Route::get('/admin/akademik/jurusan',                         [App\Http\Controllers\JurusanController::class, 'index'])->name('admin.jurusan.index');
    Route::post('/admin/akademik/jurusan',                        [App\Http\Controllers\JurusanController::class, 'store'])->name('admin.jurusan.store');
    Route::put('/admin/akademik/jurusan/{jurusan}',               [App\Http\Controllers\JurusanController::class, 'update'])->name('admin.jurusan.update');
    Route::put('/admin/akademik/jurusan/{jurusan}/assign-prodi',  [App\Http\Controllers\JurusanController::class, 'assignProdi'])->name('admin.jurusan.assign-prodi');
    Route::delete('/admin/akademik/jurusan/{jurusan}',            [App\Http\Controllers\JurusanController::class, 'destroy'])->name('admin.jurusan.destroy');

    // API: ambil prodi berdasarkan jurusan (untuk dropdown dinamis)
    Route::get('/api/jurusan/{jurusan}/prodi',             [App\Http\Controllers\ProgramStudiController::class, 'byJurusan'])->name('api.prodi.by-jurusan');

    Route::get('/admin/akademik/prodi',                    [App\Http\Controllers\ProgramStudiController::class, 'index'])->name('admin.prodi.index');
    Route::post('/admin/akademik/prodi',                   [App\Http\Controllers\ProgramStudiController::class, 'store'])->name('admin.prodi.store');
    Route::put('/admin/akademik/prodi/{prodi}',            [App\Http\Controllers\ProgramStudiController::class, 'update'])->name('admin.prodi.update');
    Route::delete('/admin/akademik/prodi/{prodi}',         [App\Http\Controllers\ProgramStudiController::class, 'destroy'])->name('admin.prodi.destroy');

    // Admin — Pengelola (multi-jabatan dosen)
    Route::get('/admin/pengelola',                  [App\Http\Controllers\PengelolaController::class, 'index'])->name('admin.pengelola.index');
    Route::post('/admin/pengelola',                 [App\Http\Controllers\PengelolaController::class, 'store'])->name('admin.pengelola.store');
    Route::put('/admin/pengelola/{pengelola}',      [App\Http\Controllers\PengelolaController::class, 'update'])->name('admin.pengelola.update');
    Route::delete('/admin/pengelola/{pengelola}',   [App\Http\Controllers\PengelolaController::class, 'destroy'])->name('admin.pengelola.destroy');

    // ─── Kaprodi Routes ─────────────────────────────────────────────
    Route::get('/kaprodi/dashboard', [App\Http\Controllers\GraduateProfileController::class, 'dashboard'])->name('kaprodi.dashboard');

    // "Kelola" — sidebar entry redirect ke sub-menu pertama; nav horizontal di top page menangani switching.
    Route::get('/kaprodi/kelola', fn() => redirect()->route('graduate-profiles.index'))->name('kaprodi.kelola.index');

    Route::resource('graduate-profiles', App\Http\Controllers\GraduateProfileController::class);
    Route::post('cpls/min-target', [App\Http\Controllers\CplController::class, 'updateMinTarget'])->name('cpls.min-target.update');
    Route::resource('cpls', App\Http\Controllers\CplController::class);
    Route::resource('courses', App\Http\Controllers\CourseController::class);
    Route::resource('classrooms', App\Http\Controllers\ClassroomController::class);
    Route::post('classrooms/{classroom}/archive', [App\Http\Controllers\ClassroomController::class, 'archive'])->name('classrooms.archive');
    Route::delete('classrooms/{classroom}/students/{student}', [App\Http\Controllers\ClassroomController::class, 'unenroll'])->name('classrooms.unenroll');

    // Kaprodi - Laporan Nilai
    Route::get('/kaprodi/laporan', [App\Http\Controllers\KaprodiController::class, 'laporanIndex'])->name('kaprodi.laporan.index');
    Route::get('/kaprodi/laporan/{classroom}', [App\Http\Controllers\KaprodiController::class, 'laporanShow'])->name('kaprodi.laporan.show');
    Route::get('/kaprodi/laporan-mahasiswa', [App\Http\Controllers\KaprodiController::class, 'laporanMahasiswa'])->name('kaprodi.laporan.mahasiswa');
    Route::get('/kaprodi/laporan-mahasiswa/{student}', [App\Http\Controllers\KaprodiController::class, 'laporanMahasiswaShow'])->name('kaprodi.laporan.mahasiswa.show');
    Route::get('/kaprodi/laporan-mahasiswa/{student}/{classroom}', [App\Http\Controllers\KaprodiController::class, 'laporanMahasiswaKelasShow'])->name('kaprodi.laporan.mahasiswa.kelas');

    // Kaprodi - Arsip Kelas
    Route::get('/kaprodi/arsip', [App\Http\Controllers\KaprodiController::class, 'arsipIndex'])->name('kaprodi.arsip.index');

    // Kaprodi - CPMK Approval (DINONAKTIFKAN SEMENTARA)
    // Route::get('/kaprodi/cpmk-approvals', [App\Http\Controllers\KaprodiController::class, 'cpmkApprovalIndex'])->name('kaprodi.cpmk-approvals.index');
    // Route::get('/kaprodi/cpmk-approvals/{classroomCpmk}', [App\Http\Controllers\KaprodiController::class, 'cpmkApprovalShow'])->name('kaprodi.cpmk-approvals.show');
    // Route::post('/kaprodi/cpmk-approvals/{classroomCpmk}/approve', [App\Http\Controllers\KaprodiController::class, 'cpmkApprove'])->name('kaprodi.cpmk-approvals.approve');
    // Route::post('/kaprodi/cpmk-approvals/{classroomCpmk}/reject', [App\Http\Controllers\KaprodiController::class, 'cpmkReject'])->name('kaprodi.cpmk-approvals.reject');

    // Kaprodi - Notifications
    Route::post('/notifications/{id}/read', function (string $id) {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return redirect()->back();
    })->name('notifications.read');
    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    })->name('notifications.readAll');

    // ─── CPMK Template Routes (Kaprodi, data lama) ──────────────────
    Route::get('cpmks/create/{course}', [App\Http\Controllers\CpmkController::class, 'create'])->name('cpmks.create');
    Route::post('cpmks', [App\Http\Controllers\CpmkController::class, 'store'])->name('cpmks.store');
    Route::get('cpmks/{cpmk}', [App\Http\Controllers\CpmkController::class, 'show'])->name('cpmks.show');
    Route::get('cpmks/{cpmk}/edit', [App\Http\Controllers\CpmkController::class, 'edit'])->name('cpmks.edit');
    Route::put('cpmks/{cpmk}', [App\Http\Controllers\CpmkController::class, 'update'])->name('cpmks.update');
    Route::delete('cpmks/{cpmk}', [App\Http\Controllers\CpmkController::class, 'destroy'])->name('cpmks.destroy');

    // ─── Dosen Routes ────────────────────────────────────────────────
    Route::get('/dosen/dashboard', [App\Http\Controllers\DosenController::class, 'dashboard'])->name('dosen.dashboard');
    Route::get('/dosen/pemetaan', [App\Http\Controllers\DosenController::class, 'pemetaan'])->name('dosen.pemetaan');
    Route::get('/dosen/riwayat', [App\Http\Controllers\DosenController::class, 'riwayat'])->name('dosen.riwayat.index');
    Route::get('/dosen/classrooms/{classroom}', [App\Http\Controllers\DosenController::class, 'show'])->name('dosen.classrooms.show');
    Route::get('/dosen/classrooms/{classroom}/report', [App\Http\Controllers\DosenController::class, 'report'])->name('dosen.classrooms.report');
    Route::post('/dosen/classrooms/{classroom}/export-satu-unri', [App\Http\Controllers\DosenController::class, 'exportSatuUnri'])->name('dosen.classrooms.export-satu-unri');

    // Dosen - Classroom CPMK (per semester, dibuat dosen)
    Route::get('/dosen/classrooms/{classroom}/cpmks/create', [App\Http\Controllers\ClassroomCpmkController::class, 'create'])->name('dosen.classroom-cpmks.create');
    Route::post('/dosen/classrooms/{classroom}/cpmks', [App\Http\Controllers\ClassroomCpmkController::class, 'store'])->name('dosen.classroom-cpmks.store');
    Route::get('/dosen/classroom-cpmks/{classroomCpmk}/edit', [App\Http\Controllers\ClassroomCpmkController::class, 'edit'])->name('dosen.classroom-cpmks.edit');
    Route::put('/dosen/classroom-cpmks/{classroomCpmk}', [App\Http\Controllers\ClassroomCpmkController::class, 'update'])->name('dosen.classroom-cpmks.update');
    Route::delete('/dosen/classroom-cpmks/{classroomCpmk}', [App\Http\Controllers\ClassroomCpmkController::class, 'destroy'])->name('dosen.classroom-cpmks.destroy');
    Route::post('/dosen/classroom-cpmks/{classroomCpmk}/submit', [App\Http\Controllers\ClassroomCpmkController::class, 'submitApproval'])->name('dosen.classroom-cpmks.submit');

    // Dosen - Indicators & Assessments (per ClassroomCpmkIndicator)
    Route::get('/dosen/indicators/{indicator}/edit', [App\Http\Controllers\DosenController::class, 'editIndicator'])->name('dosen.indicators.edit');
    Route::post('/dosen/indicators/{indicator}/components', [App\Http\Controllers\DosenController::class, 'storeComponents'])->name('dosen.components.store');

    // Dosen - Components for old Indicator model (kaprodi-defined CPMKs)
    Route::post('/dosen/old-indicators/{indicator}/components', [App\Http\Controllers\DosenController::class, 'storeIndicatorComponents'])->name('dosen.indicator.components.store');

    // Dosen - Score Entry
    Route::get('/dosen/assessments/{assessment}/scores', [App\Http\Controllers\DosenController::class, 'scoresIndex'])->name('dosen.assessments.scores.index');
    Route::post('/dosen/assessments/{assessment}/scores', [App\Http\Controllers\DosenController::class, 'storeScores'])->name('dosen.assessments.scores.store');

    // Assessment Score (old Assessment model, kaprodi-defined CPMKs)
    Route::get('/assessments/{assessment}/scores', [App\Http\Controllers\AssessmentScoreController::class, 'index'])->name('assessments.scores.index');
    Route::post('/assessments/{assessment}/scores', [App\Http\Controllers\AssessmentScoreController::class, 'store'])->name('assessments.scores.store');

    // ─── Mahasiswa Routes ────────────────────────────────────────────
    Route::get('/mahasiswa/dashboard', [App\Http\Controllers\MahasiswaController::class, 'dashboard'])->name('mahasiswa.dashboard');
    Route::post('/mahasiswa/enroll', [App\Http\Controllers\MahasiswaController::class, 'enroll'])->name('mahasiswa.enroll');
    Route::get('/mahasiswa/classrooms/{classroom}', [App\Http\Controllers\MahasiswaController::class, 'show'])->name('mahasiswa.classrooms.show');
    Route::get('/mahasiswa/riwayat-kelas', [App\Http\Controllers\MahasiswaController::class, 'riwayatKelas'])->name('mahasiswa.riwayat');
    Route::get('/mahasiswa/transkrip', [App\Http\Controllers\MahasiswaController::class, 'transkrip'])->name('mahasiswa.transkrip');
    Route::get('/mahasiswa/transkrip/download/konvensional', [App\Http\Controllers\MahasiswaController::class, 'downloadKonvensional'])->name('mahasiswa.transkrip.download.konvensional');
    Route::get('/mahasiswa/transkrip/download/obe', [App\Http\Controllers\MahasiswaController::class, 'downloadObe'])->name('mahasiswa.transkrip.download.obe');
    Route::get('/mahasiswa/khs', [App\Http\Controllers\MahasiswaController::class, 'khs'])->name('mahasiswa.khs');

    // Fallback Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Preview layout pilot (Bootstrap sidebar) — sementara untuk review desain.
    Route::get('/preview-layout', fn() => view('preview-layout'))->name('preview.layout');

    Route::post('/role-switch', [App\Http\Controllers\RoleSwitchController::class, 'switch'])
    ->name('role.switch');
});