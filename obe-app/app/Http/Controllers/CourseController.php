<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Course;
use App\Models\Cpmk;
use App\Models\Indicator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $semester = $request->input('semester');
        $auth     = Auth::user();

        $query = Course::with(['users', 'cpmks.lecturer', 'cpmks.cpl']);

        if ($semester) {
            $query->where('semester', $semester);
        }

        // ── Scope per role yang sedang login ──────────────────────────────
        if ($auth) {
            if ($auth->role === 'kaprodi') {
                $prodiId   = $auth->activeProdiId();
                $jurusanId = $auth->jurusan_id;

                if ($prodiId) {
                    // Scope KETAT: hanya course yang eksplisit milik prodi ini
                    // Tidak ada fallback NULL agar kaprodi prodi lain tidak ikut melihat
                    $query->where('program_studi_id', $prodiId);
                } elseif ($jurusanId) {
                    // Kaprodi belum dikonfigurasi prodinya → fallback ke jurusan
                    $query->where('jurusan_id', $jurusanId);
                }
            } elseif ($auth->role === 'admin_jurusan' && $auth->jurusan_id) {
                $query->where('jurusan_id', $auth->jurusan_id);
            }
        }

        $courses = $query->orderBy('semester')
                         ->orderBy('code')
                         ->get();

        $allCourses = Course::orderBy('code')->get(['id', 'code', 'name']);

        return view('kaprodi.courses.index', compact('courses', 'allCourses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('code')->get();

        $auth    = Auth::user();
        $prodiId = $auth?->activeProdiId();

        // CPL hanya dari prodi kaprodi yang login (tanpa fallback NULL)
        $cplQuery = Cpl::orderBy('code');
        if ($prodiId) {
            $cplQuery->where('program_studi_id', $prodiId);
        }
        $cpls = $cplQuery->get();

        $lecturerQuery = \App\Models\User::where('role', 'dosen')->orderBy('name');
        // Admin jurusan: hanya dosen dari jurusannya. Kaprodi: semua dosen.
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $lecturerQuery->where('jurusan_id', $auth->jurusan_id);
        }
        $lecturers = $lecturerQuery->get();

        return view('kaprodi.courses.create', compact('courses', 'cpls', 'lecturers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                   => 'required|string|unique:obe_mata_kuliah,code',
            'name'                   => 'required|string',
            'sks'                    => 'required|integer|min:1',
            'semester'               => 'required|integer|min:1|max:8',
            'wajib_pilihan'          => 'required|in:W,P',
            'prerequisite_course_id' => 'nullable|exists:obe_mata_kuliah,id',
        ]);

        DB::beginTransaction();
        try {
            $auth   = Auth::user();
            $course = Course::create([
                'jurusan_id'             => $auth?->jurusan_id ?? null,
                'program_studi_id'       => $auth?->activeProdiId() ?? null,
                'code'                   => $validated['code'],
                'name'                   => $validated['name'],
                'sks'                    => $validated['sks'],
                'semester'               => $validated['semester'],
                'wajib_pilihan'          => $validated['wajib_pilihan'],
                'prerequisite_course_id' => $validated['prerequisite_course_id'] ?? null,
            ]);

            // Save CPMKs from hidden inputs (added via modal)
            $cpmksInput = $request->input('cpmks', []);
            $meeting    = 1;
            $total      = 16;
            foreach ($cpmksInput as $cpmkData) {
                if (empty($cpmkData['code']) || empty($cpmkData['description'])) continue;

                $pct   = (float) ($cpmkData['percentage'] ?? 0);
                $count = max(1, (int) round(($pct / 100) * $total));

                $cpmk = Cpmk::create([
                    'course_id'     => $course->id,
                    'cpl_id'        => $cpmkData['cpl_id'] ?: null,
                    'code'          => $cpmkData['code'],
                    'description'   => $cpmkData['description'],
                    'percentage'    => $pct,
                    'meeting_start' => $meeting,
                    'meeting_end'   => $meeting + $count - 1,
                ]);
                $meeting += $count;

                // Save indicators — dukung mode otomatis, manual, dan campuran.
                $rows = [];
                foreach ($cpmkData['indicators'] ?? [] as $indData) {
                    $desc = trim((string) ($indData['description'] ?? ''));
                    if ($desc === '') continue;
                    $pctRaw = $indData['percentage'] ?? null;
                    $hasPct = $pctRaw !== null && $pctRaw !== '' && is_numeric($pctRaw);
                    $rows[] = ['desc' => $desc, 'pct' => $hasPct ? (float) $pctRaw : null];
                }

                $iwt = $cpmkData['indicator_weight_type'] ?? null;
                if ($iwt === null) {
                    $iwt = collect($rows)->contains(fn($r) => $r['pct'] !== null) ? 'manual' : 'otomatis';
                }

                $rowCount = count($rows);
                if ($rowCount > 0 && $iwt === 'otomatis') {
                    $per = round(100 / $rowCount, 2);
                    foreach ($rows as $r) {
                        Indicator::create(['cpmk_id' => $cpmk->id, 'description' => $r['desc'], 'percentage' => $per]);
                    }
                } elseif ($rowCount > 0) {
                    $manualTotal = array_sum(array_column(array_filter($rows, fn($r) => $r['pct'] !== null), 'pct'));
                    $autoCount   = count(array_filter($rows, fn($r) => $r['pct'] === null));
                    $autoEach    = $autoCount > 0 ? max(0, round((100 - $manualTotal) / $autoCount, 2)) : 0;
                    foreach ($rows as $r) {
                        Indicator::create([
                            'cpmk_id'     => $cpmk->id,
                            'description' => $r['desc'],
                            'percentage'  => $r['pct'] ?? $autoEach,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        return redirect()->route('courses.index')
            ->with('success', 'Mata Kuliah berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $course->load(['cpls', 'prerequisite', 'cpmks.cpl', 'cpmks.lecturer', 'cpmks.indicators']);

        $auth    = Auth::user();
        $prodiId = $auth?->activeProdiId();

        // CPL hanya dari prodi kaprodi yang login (tanpa fallback NULL)
        $cplQuery = Cpl::orderBy('code');
        if ($prodiId) {
            $cplQuery->where('program_studi_id', $prodiId);
        }
        $cpls = $cplQuery->get();

        $lecturerQuery = \App\Models\User::where('role', 'dosen')->orderBy('name');
        // Admin jurusan: hanya dosen dari jurusannya. Kaprodi: semua dosen.
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $lecturerQuery->where('jurusan_id', $auth->jurusan_id);
        }
        $lecturers = $lecturerQuery->get();

        $prereqCourses = Course::where('id', '!=', $course->id)->orderBy('code')->get(['id', 'code', 'name']);
        return view('kaprodi.courses.show', compact('course', 'cpls', 'lecturers', 'prereqCourses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        // Form sekarang berupa modal di halaman index.
        return redirect()->route('courses.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code'                   => 'required|string|unique:obe_mata_kuliah,code,' . $course->id,
            'name'                   => 'required|string',
            'sks'                    => 'required|integer|min:1',
            'semester'               => 'required|integer|min:1|max:8',
            'wajib_pilihan'          => 'required|in:W,P',
            'prerequisite_course_id' => 'nullable|exists:obe_mata_kuliah,id',
        ]);

        $course->update([
            'code'                   => $validated['code'],
            'name'                   => $validated['name'],
            'sks'                    => $validated['sks'],
            'semester'               => $validated['semester'],
            'wajib_pilihan'          => $validated['wajib_pilihan'],
            'prerequisite_course_id' => $validated['prerequisite_course_id'] ?? null,
        ]);

        return redirect()->route('courses.index')->with('success', 'Mata Kuliah berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('courses.index')->with('success', 'Mata Kuliah berhasil dihapus.');
    }
}