<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');
    
    // User Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');

    // Admin Routes
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('users', App\Http\Controllers\UserController::class);
    
    // Admin Settings Route
    Route::get('/admin/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('/admin/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Kaprodi Routes - Graduate Profiles CRUD
    Route::get('/kaprodi/dashboard', [App\Http\Controllers\GraduateProfileController::class, 'dashboard'])->name('kaprodi.dashboard');
    Route::resource('graduate-profiles', App\Http\Controllers\GraduateProfileController::class);
    
    Route::resource('graduate-profiles', App\Http\Controllers\GraduateProfileController::class);
    
    // Kaprodi Routes - CPL CRUD
    Route::resource('cpls', App\Http\Controllers\CplController::class);
    Route::resource('courses', App\Http\Controllers\CourseController::class);
    Route::resource('classrooms', App\Http\Controllers\ClassroomController::class);
    Route::post('classrooms/{classroom}/archive', [App\Http\Controllers\ClassroomController::class, 'archive'])->name('classrooms.archive');
    Route::delete('classrooms/{classroom}/students/{student}', [App\Http\Controllers\ClassroomController::class, 'unenroll'])->name('classrooms.unenroll');
    
    // CPMK Routes
    Route::get('cpmks/create/{course}', [App\Http\Controllers\CpmkController::class, 'create'])->name('cpmks.create');
    Route::post('cpmks', [App\Http\Controllers\CpmkController::class, 'store'])->name('cpmks.store');
    Route::get('cpmks/{cpmk}', [App\Http\Controllers\CpmkController::class, 'show'])->name('cpmks.show');
    Route::get('cpmks/{cpmk}/edit', [App\Http\Controllers\CpmkController::class, 'edit'])->name('cpmks.edit');
    Route::put('cpmks/{cpmk}', [App\Http\Controllers\CpmkController::class, 'update'])->name('cpmks.update');
    Route::delete('cpmks/{cpmk}', [App\Http\Controllers\CpmkController::class, 'destroy'])->name('cpmks.destroy');

    // Dosen Routes
    Route::get('/dosen/dashboard', [App\Http\Controllers\DosenController::class, 'dashboard'])->name('dosen.dashboard');
    Route::get('/dosen/courses/{course}', [App\Http\Controllers\DosenController::class, 'show'])->name('dosen.courses.show');
    Route::get('/dosen/indicators/{indicator}/edit', [App\Http\Controllers\DosenController::class, 'editIndicator'])->name('dosen.indicators.edit');
    
    // Assessment Component Routes
    Route::resource('assessments', App\Http\Controllers\AssessmentController::class)->only(['store', 'update', 'destroy']);
    Route::get('assessments/{assessment}/scores', [App\Http\Controllers\AssessmentScoreController::class, 'index'])->name('assessments.scores.index');
    Route::post('assessments/{assessment}/scores', [App\Http\Controllers\AssessmentScoreController::class, 'store'])->name('assessments.scores.store');

    // Mahasiswa Routes
    Route::get('/mahasiswa/dashboard', [App\Http\Controllers\MahasiswaController::class, 'dashboard'])->name('mahasiswa.dashboard');
    Route::post('/mahasiswa/enroll', [App\Http\Controllers\MahasiswaController::class, 'enroll'])->name('mahasiswa.enroll');

    // Fallback/Generic Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
