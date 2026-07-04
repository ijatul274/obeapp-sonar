<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    private const GANJIL = [1, 3, 5, 7];
    private const GENAP  = [2, 4, 6, 8];

    private function activePeriod(): array
    {
        return Classroom::currentPeriod();
    }

    /* ── Index ───────────────────────────────────────────────── */
    public function index(Request $request)
    {
        Classroom::autoArchiveExpired();

        $period      = $activePeriod = $this->activePeriod();
        $years = Classroom::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        $query = Classroom::with(['course.cpmks', 'cpmkLecturers', 'cpmks'])
            ->where('is_archived', false);

        $filterYear = $request->input('academic_year', $activePeriod['academic_year']);
        if ($filterYear) {
            $query->where('academic_year', $filterYear);
        }
        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // ── Filter kelas berdasarkan role yang sedang login ──────────────
        $auth = Auth::user();

        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            // Admin jurusan: kelas dari jurusannya saja
            $jurusanId = $auth->jurusan_id;
            $query->where(function ($q) use ($jurusanId) {
                $q->whereHas('lecturer', fn($lq) => $lq->where('jurusan_id', $jurusanId))
                  ->orWhereHas('cpmkLecturers', fn($lq) => $lq->where('jurusan_id', $jurusanId));
            });
        } elseif ($auth && $auth->role === 'kaprodi' && $auth->jurusan_id) {
            // Kaprodi: kelas dari jurusannya — dosen lintas prodi dalam jurusan sama tetap
            // bisa mengajar, sehingga scope by jurusan sudah cukup representatif
            $jurusanId = $auth->jurusan_id;
            $query->where(function ($q) use ($jurusanId) {
                $q->whereHas('lecturer', fn($lq) => $lq->where('jurusan_id', $jurusanId))
                  ->orWhereHas('cpmkLecturers', fn($lq) => $lq->where('jurusan_id', $jurusanId));
            });
        }

        $classrooms = $query->orderBy('period_type')->orderBy('semester')->orderBy('name')->get();
        $allCourses = Course::with('cpmks')->orderBy('semester')->orderBy('name')->get();

        // ── Dropdown dosen: hanya dosen dari jurusan yang sama ────────────
        $dosenQuery = User::dosenAkademik()->orderBy('name');
        if ($auth && in_array($auth->role, ['admin_jurusan', 'kaprodi']) && $auth->jurusan_id) {
            $dosenQuery->where('jurusan_id', $auth->jurusan_id);
        }
        $dosens = $dosenQuery->get();

        $jsCoursesData = $allCourses->map(fn($c) => [
            'id'   => (string) $c->id,
            'code' => $c->code,
            'name' => $c->name,
            'sem'  => $c->semester,
        ])->values();

        $jsCpmkData = $allCourses->flatMap(fn($c) => $c->cpmks->map(fn($cp) => [
            'id'        => (string) $cp->id,
            'course_id' => (string) $c->id,
            'code'      => $cp->code,
            'name'      => $cp->code . ' — ' . \Str::limit($cp->description, 60),
            'meetings'  => $cp->meeting_range,
        ]))->values();

        return view('kaprodi.classrooms.index', compact(
            'classrooms', 'years', 'filterYear', 'activePeriod',
            'allCourses', 'dosens', 'jsCoursesData', 'jsCpmkData'
        ));
    }

    /* ── Create ──────────────────────────────────────────────── */
    public function create(Request $request)
    {
        return redirect()->route('classrooms.index');
    }

    /* ── Store ───────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'course_id'        => 'required|exists:obe_mata_kuliah,id',
            'academic_year'    => 'required|string|max:9',
            'period_type'      => 'required|in:ganjil,genap',
            'lecturer_id'      => 'nullable|exists:obe_pengguna,id',   // ← tambah
            'cpmk_lecturers'   => 'nullable|array',
            'cpmk_lecturers.*' => 'nullable|exists:obe_pengguna,id',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $validated['semester'] = $course->semester;

        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (Classroom::where('enrollment_code', $code)->exists());

        $validated['enrollment_code'] = $code;
        $validated['is_archived']     = false;

        // ── Perbaikan 2: simpan lecturer_id ke obe_kelas ──────────────
        $classroom = Classroom::create([
            'name'            => $validated['name'],
            'course_id'       => $validated['course_id'],
            'academic_year'   => $validated['academic_year'],
            'period_type'     => $validated['period_type'],
            'semester'        => $validated['semester'],
            'enrollment_code' => $validated['enrollment_code'],
            'is_archived'     => $validated['is_archived'],
            'lecturer_id'     => $validated['lecturer_id'] ?? null,   // ← tambah
        ]);

        if (!empty($validated['cpmk_lecturers'])) {
            $syncData = [];
            foreach ($validated['cpmk_lecturers'] as $cpmkId => $lecturerId) {
                if ($lecturerId) {
                    $syncData[$cpmkId] = ['lecturer_id' => $lecturerId];
                }
            }
            $classroom->cpmks()->sync($syncData);
        }

        return redirect()->route('classrooms.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    /* ── Show ────────────────────────────────────────────────── */
    public function show(Classroom $classroom)
    {
        $classroom->load(['course.cpmks', 'lecturer', 'students']);
        return view('kaprodi.classrooms.show', compact('classroom'));
    }

    /* ── Edit ────────────────────────────────────────────────── */
    public function edit(Classroom $classroom)
    {
        $classroom->load(['course.cpmks', 'lecturer', 'students', 'cpmks']);

        // ── Dropdown dosen: hanya dosen dari jurusan yang sama ────────────
        $auth = Auth::user();
        $dosenQuery = User::dosenAkademik()->orderBy('name');
        if ($auth && in_array($auth->role, ['admin_jurusan', 'kaprodi']) && $auth->jurusan_id) {
            $dosenQuery->where('jurusan_id', $auth->jurusan_id);
        }
        $dosens = $dosenQuery->get();

        $semesterList = ($classroom->period_type === 'ganjil') ? self::GANJIL : self::GENAP;
        $courses = Course::whereIn('semester', $semesterList)
            ->orderBy('semester')
            ->orderBy('name')
            ->get();

        // Peta cpmk_id => lecturer_id yang sudah tersimpan
        $cpmkLecturerMap = $classroom->cpmks->mapWithKeys(fn($cp) => [
            (string) $cp->id => (string) ($cp->pivot->lecturer_id ?? ''),
        ])->toArray();

        return view('kaprodi.classrooms.edit', compact('classroom', 'dosens', 'courses', 'cpmkLecturerMap'));
    }

    /* ── Update ──────────────────────────────────────────────── */
    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'course_id'        => 'required|exists:obe_mata_kuliah,id',
            'lecturer_id'      => 'nullable|exists:obe_pengguna,id',   // ← tambah
            'cpmk_lecturers'   => 'nullable|array',
            'cpmk_lecturers.*' => 'nullable|exists:obe_pengguna,id',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $validated['semester'] = $course->semester;

        // ── Perbaikan 2 (update): simpan lecturer_id ───────────────────
        $classroom->update([
            'name'        => $validated['name'],
            'course_id'   => $validated['course_id'],
            'semester'    => $validated['semester'],
            'lecturer_id' => $validated['lecturer_id'] ?? null,        // ← tambah
        ]);

        // Hanya sync CPMK jika form mengirimkan field cpmk_lecturers
        // (field ini ada di modal index, tapi tidak ada di halaman edit.blade.php)
        if ($request->has('cpmk_lecturers')) {
            $syncData = [];
            foreach (($validated['cpmk_lecturers'] ?? []) as $cpmkId => $lecturerId) {
                if ($lecturerId) {
                    $syncData[$cpmkId] = ['lecturer_id' => $lecturerId];
                }
            }
            $classroom->cpmks()->sync($syncData);

            // Jika request dari halaman edit (bukan modal index), redirect kembali ke edit
            if ($request->headers->get('referer') && str_contains($request->headers->get('referer'), '/edit')) {
                return redirect()->route('classrooms.edit', $classroom)
                    ->with('success', 'Penugasan CPMK berhasil disimpan.');
            }
        }
        // Jika 'cpmk_lecturers' tidak ada di request, jangan sentuh penugasan CPMK yang sudah ada

        return redirect()->route('classrooms.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    /* ── Archive ─────────────────────────────────────────────── */
    public function archive(Classroom $classroom)
    {
        $willArchive = !$classroom->is_archived;
        $payload = ['is_archived' => $willArchive];

        if ($willArchive) {
            $payload['kaprodi_snapshot'] = auth()->user()?->name;
            $payload['archived_at']      = now();
        }
        $classroom->update($payload);

        $message = $classroom->is_archived
            ? 'Kelas berhasil diarsipkan.'
            : 'Kelas berhasil dikembalikan dari arsip.';

        return redirect()->route('classrooms.index')->with('success', $message);
    }

    /* ── Unenroll student ────────────────────────────────────── */
    public function unenroll(Classroom $classroom, $studentId)
    {
        $classroom->students()->detach($studentId);
        return redirect()->back()->with('success', 'Mahasiswa berhasil dihapus dari kelas.');
    }

    /* ── Destroy ─────────────────────────────────────────────── */
    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil dihapus.');
    }
}