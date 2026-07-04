<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomCpmk;
use App\Models\User;
use App\Notifications\CpmkStatusChanged;
use App\Services\CplAchievementService;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaprodiController extends Controller
{
    /* ── Helper: scope query kelas berdasarkan prodi kaprodi yang login ── */

    /**
     * Scope classroom query agar hanya menampilkan kelas milik prodi kaprodi.
     * Tidak ada fallback ke jurusan — data NULL program_studi_id tidak ikut tampil.
     * Ini memastikan kaprodi S1 TE tidak melihat data TI meskipun satu jurusan.
     */
    private function applyKaprodiScope($query, User $auth): void
    {
        $prodiId   = $auth->activeProdiId();
        $jurusanId = $auth->jurusan_id;

        if ($prodiId) {
            // Scope KETAT: hanya kelas yang course-nya eksplisit milik prodi ini
            $query->whereHas('course', fn($cq) =>
                $cq->where('program_studi_id', $prodiId)
            );
        } elseif ($jurusanId) {
            // Kaprodi belum dikonfigurasi prodinya → fallback ke jurusan
            // Ini sebaiknya tidak terjadi jika admin sudah set prodi kaprodi dengan benar.
            $query->where(function ($q) use ($jurusanId) {
                $q->whereHas('lecturer', fn($lq) => $lq->where('jurusan_id', $jurusanId))
                  ->orWhereHas('cpmkLecturers', fn($lq) => $lq->where('jurusan_id', $jurusanId));
            });
        }
    }

    /* ── Helper: Query builder kelas ──────────────────────────────────── */
    private function classroomQuery(Request $request, bool $archived = false)
    {
        $period       = Classroom::currentPeriod();
        $activePeriod = $period;

        $years = Classroom::select('academic_year')
            ->whereNotNull('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        $query = Classroom::with(['course.cpmks', 'cpmkLecturers', 'cpmks'])
            ->where('is_archived', $archived);

        $filterYear = $request->input('academic_year', $archived ? null : $activePeriod['academic_year']);
        if ($filterYear) {
            $query->where('academic_year', $filterYear);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('semester')) {
            $query->whereHas('course', fn($q) => $q->where('semester', $request->semester));
        }

        // ── Scope per role yang sedang login ──────────────────────────────
        $auth = Auth::user();

        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $jurusanId = $auth->jurusan_id;
            $query->where(function ($q) use ($jurusanId) {
                $q->whereHas('lecturer', fn($lq) => $lq->where('jurusan_id', $jurusanId))
                  ->orWhereHas('cpmkLecturers', fn($lq) => $lq->where('jurusan_id', $jurusanId));
            });
        } elseif ($auth && $auth->role === 'kaprodi') {
            $this->applyKaprodiScope($query, $auth);
        }

        $classrooms = $query->orderBy('period_type')->orderBy('name')->get();

        return compact('classrooms', 'years', 'filterYear', 'activePeriod');
    }

    /* ── Laporan: Index ────────────────────────────────────────────────── */
    public function laporanIndex(Request $request)
    {
        $data = $this->classroomQuery($request, false);
        return view('kaprodi.laporan.index', $data);
    }

    /* ── Laporan: Show (detail nilai) ──────────────────────────────────── */
    public function laporanShow(Classroom $classroom)
    {
        $course = $classroom->course;

        $cpmks = $course
            ? $course->cpmks()->with([
                'cpl',
                'indicators' => fn($q) => $q->orderBy('id'),
                'indicators.assessments' => fn($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'indicators.assessments.scores',
            ])->orderBy('id')->get()
            : collect();

        $students = $classroom->students()->orderBy('identity')->get();

        $scoreMap = [];
        foreach ($cpmks as $cpmk) {
            foreach ($cpmk->indicators as $ind) {
                foreach ($ind->assessments as $asmnt) {
                    foreach ($asmnt->scores as $sc) {
                        $scoreMap[$asmnt->id][$sc->student_id] = (float) $sc->score;
                    }
                }
            }
        }

        $rows    = $this->buildRows($students, $cpmks, $scoreMap);
        $cplRows = CplAchievementService::perClassroom($rows, $cpmks);

        return view('kaprodi.laporan.show', compact('classroom', 'course', 'rows', 'cpmks', 'cplRows'));
    }

    /* ── Laporan: Mahasiswa (ketercapaian CPL per mahasiswa) ────────────── */
    public function laporanMahasiswa(Request $request)
    {
        $auth    = Auth::user();
        $prodiId = $auth->activeProdiId();

        // CPL hanya dari prodi kaprodi yang sedang login (tanpa fallback NULL)
        $cplQuery = \App\Models\Cpl::orderBy('code');
        if ($prodiId) {
            $cplQuery->where('program_studi_id', $prodiId);
        }
        $cpls = $cplQuery->get();

        $filterAngkatan = $request->input('angkatan');

        // Ambil kelas yang sudah di-scope ke prodi kaprodi
        $classroomQuery = Classroom::with([
            'course.cpmks.cpl',
            'course.cpmks.indicators.assessments',
        ]);

        if ($auth->role === 'kaprodi') {
            $this->applyKaprodiScope($classroomQuery, $auth);
        }

        $classrooms = $classroomQuery->get();

        $allStudentIds = $classrooms->flatMap(fn($c) => $c->students->pluck('id'))->unique();

        $studentQuery = \App\Models\User::whereIn('id', $allStudentIds)
            ->with('profilMahasiswa')
            ->orderBy('identity');

        if ($prodiId) {
            $studentQuery->whereHas('profilMahasiswa', fn($mq) =>
                $mq->where('program_studi_id', $prodiId)
            );
        }

        $allStudents = $studentQuery->get();

        if ($filterAngkatan) {
            $suffix = substr($filterAngkatan, -2);
            $allStudents = $allStudents->filter(function ($student) use ($suffix) {
                $nim = $student->profilMahasiswa?->nim ?? $student->identity ?? '';
                return substr($nim, 0, 2) === $suffix;
            })->values();
        }

        $angkatanBaseQuery = \App\Models\User::whereIn('id', $allStudentIds)
            ->with('profilMahasiswa');

        if ($prodiId) {
            $angkatanBaseQuery->whereHas('profilMahasiswa', fn($mq) =>
                $mq->where('program_studi_id', $prodiId)
            );
        }

        $angkatanList = $angkatanBaseQuery->get()
            ->map(function ($s) {
                $nim = $s->profilMahasiswa?->nim ?? $s->identity ?? '';
                $prefix = substr($nim, 0, 2);
                return is_numeric($prefix) ? '20' . $prefix : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $scoreMap = [];
        foreach ($classrooms as $classroom) {
            $cid    = $classroom->id;
            $course = $classroom->course;
            if (!$course) continue;
            foreach ($course->cpmks as $cpmk) {
                foreach ($cpmk->indicators as $ind) {
                    $assessments = $ind->assessments()
                        ->where('classroom_id', $cid)
                        ->with('scores')
                        ->get();
                    foreach ($assessments as $asmnt) {
                        foreach ($asmnt->scores as $sc) {
                            $scoreMap[$cid][$asmnt->id][$sc->student_id] = (float) $sc->score;
                        }
                    }
                }
            }
        }

        $cplPerStudent = [];
        foreach ($classrooms as $classroom) {
            $cid    = $classroom->id;
            $course = $classroom->course;
            if (!$course) continue;

            $students = $classroom->students;

            foreach ($course->cpmks as $cpmk) {
                $cplId = $cpmk->cpl?->id;
                if (!$cplId) continue;

                foreach ($students as $student) {
                    $sid       = $student->id;
                    $cpmkScore = 0;
                    $hasScore  = false;

                    foreach ($cpmk->indicators as $ind) {
                        $indWeight   = (float) $ind->percentage / 100;
                        $assessments = $ind->assessments()
                            ->where('classroom_id', $cid)
                            ->get();
                        $indScore = 0;
                        $indHas   = false;

                        foreach ($assessments as $asmnt) {
                            $compWeight = (float) $asmnt->percentage / 100;
                            $raw        = $scoreMap[$cid][$asmnt->id][$sid] ?? null;
                            if ($raw !== null) {
                                $indScore += $raw * $compWeight;
                                $indHas    = true;
                            }
                        }

                        if ($indHas) {
                            $cpmkScore += $indScore * $indWeight;
                            $hasScore   = true;
                        }
                    }

                    if ($hasScore) {
                        $cplPerStudent[$sid][$cplId][] = round($cpmkScore, 2);
                    }
                }
            }
        }

        $studentCplMap = [];
        foreach ($allStudents as $student) {
            $sid = $student->id;
            foreach ($cpls as $cpl) {
                $scores = $cplPerStudent[$sid][$cpl->id] ?? [];
                $studentCplMap[$sid][$cpl->id] = count($scores)
                    ? round(array_sum($scores) / count($scores), 1)
                    : null;
            }
        }

        return view('kaprodi.laporan.mahasiswa', compact(
            'cpls', 'allStudents', 'studentCplMap', 'angkatanList', 'filterAngkatan'
        ));
    }

    /* ── Laporan: Detail Mahasiswa (daftar kelas + nilai per kelas) ────── */
    public function laporanMahasiswaShow(User $student)
    {
        $auth    = Auth::user();
        $prodiId = $auth->activeProdiId();

        // Ambil semua kelas yang diikuti mahasiswa ini, scope ke prodi kaprodi
        $classroomQuery = $student->classrooms()->with([
            'course.cpmks.cpl',
            'course.cpmks.indicators.assessments.scores',
        ]);

        if ($prodiId) {
            $classroomQuery->whereHas('course', fn($q) =>
                $q->where('program_studi_id', $prodiId)
            );
        }

        $classrooms = $classroomQuery->orderByDesc('academic_year')->orderBy('name')->get();

        // Hitung nilai per kelas untuk mahasiswa ini
        $classroomResults = [];
        foreach ($classrooms as $classroom) {
            $course = $classroom->course;
            if (!$course) {
                $classroomResults[$classroom->id] = null;
                continue;
            }

            $cpmks = $course->cpmks;
            $scoreMap = [];
            foreach ($cpmks as $cpmk) {
                foreach ($cpmk->indicators as $ind) {
                    foreach ($ind->assessments as $asmnt) {
                        foreach ($asmnt->scores as $sc) {
                            $scoreMap[$asmnt->id][$sc->student_id] = (float) $sc->score;
                        }
                    }
                }
            }

            $sid = $student->id;
            $cpmkResults = [];
            $cpmkScoresForFinal = [];
            $anyFailed = false;

            foreach ($cpmks as $cpmk) {
                $cpmkWeight = (float) $cpmk->percentage / 100;
                $cpmkScore  = 0;
                $hasScore   = false;

                foreach ($cpmk->indicators as $ind) {
                    $indWeight = (float) $ind->percentage / 100;
                    $indScore  = 0;
                    $indHas    = false;
                    foreach ($ind->assessments as $asmnt) {
                        $compWeight = (float) $asmnt->percentage / 100;
                        $raw = $scoreMap[$asmnt->id][$sid] ?? null;
                        if ($raw !== null) {
                            $indScore += $raw * $compWeight;
                            $indHas    = true;
                        }
                    }
                    if ($indHas) {
                        $cpmkScore += $indScore * $indWeight;
                        $hasScore   = true;
                    }
                }

                $lulus = $hasScore && $cpmkScore >= 70;
                if ($hasScore && !$lulus) $anyFailed = true;

                $cpmkResults[] = [
                    'cpmk'  => $cpmk,
                    'score' => $hasScore ? round($cpmkScore, 1) : null,
                    'lulus' => $lulus,
                ];
                if ($hasScore) {
                    $cpmkScoresForFinal[] = $cpmkScore * $cpmkWeight;
                }
            }

            $finalScore = $anyFailed ? 0 : (count($cpmkScoresForFinal) ? array_sum($cpmkScoresForFinal) : null);
            $grade = GradeService::toKonvensional($anyFailed ? 0 : ($finalScore ?? 0));

            $classroomResults[$classroom->id] = [
                'cpmkResults' => $cpmkResults,
                'finalScore'  => $finalScore,
                'finalMutu'   => $grade['mutu'],
                'finalGrade'  => $grade['huruf'],
                'anyFailed'   => $anyFailed,
            ];
        }

        // CPL summary untuk mahasiswa ini
        $cplQuery = \App\Models\Cpl::orderBy('code');
        if ($prodiId) $cplQuery->where('program_studi_id', $prodiId);
        $cpls = $cplQuery->get();

        $cplScores = [];
        foreach ($classrooms as $classroom) {
            $course = $classroom->course;
            if (!$course) continue;
            foreach ($course->cpmks as $cpmk) {
                $cplId = $cpmk->cpl?->id;
                if (!$cplId) continue;
                $res = collect($classroomResults[$classroom->id]['cpmkResults'] ?? [])
                    ->firstWhere('cpmk.id', $cpmk->id);
                if ($res && $res['score'] !== null) {
                    $cplScores[$cplId][] = $res['score'];
                }
            }
        }
        $cplSummary = [];
        foreach ($cpls as $cpl) {
            $scores = $cplScores[$cpl->id] ?? [];
            $cplSummary[$cpl->id] = count($scores)
                ? round(array_sum($scores) / count($scores), 1)
                : null;
        }

        return view('kaprodi.laporan.mahasiswa-detail', compact(
            'student', 'classrooms', 'classroomResults', 'cpls', 'cplSummary'
        ));
    }


    /* ── Laporan: Detail Nilai Mahasiswa per Kelas ─────────────────────── */
    public function laporanMahasiswaKelasShow(User $student, Classroom $classroom)
    {
        $auth    = Auth::user();
        $prodiId = $auth->activeProdiId();
        

        // Pastikan mahasiswa terdaftar di kelas ini
        abort_unless($classroom->students()->where('user_id', $student->id)->exists(), 403);

        // Pastikan kelas dalam scope prodi kaprodi
        if ($prodiId) {
            abort_unless(
                optional($classroom->course)->program_studi_id == $prodiId,
                403
            );
        }

        $course = $classroom->course;

        $cpmks = $course
            ? $course->cpmks()->with([
                'cpl',
                'indicators.assessments' => fn($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'indicators.assessments.scores',
            ])->orderBy('id')->get()
            : collect();

        // Pre-index scores
        $scoreMap = [];
        foreach ($cpmks as $cpmk) {
            foreach ($cpmk->indicators as $ind) {
                foreach ($ind->assessments as $asmnt) {
                    foreach ($asmnt->scores as $sc) {
                        $scoreMap[$asmnt->id][$sc->student_id] = (float) $sc->score;
                    }
                }
            }
        }

        $sid = $student->id;
        $cpmkResults        = [];
        $cpmkScoresForFinal = [];

        foreach ($cpmks as $cpmk) {
            $cpmkWeight       = (float) $cpmk->percentage / 100;
            $indicatorResults = [];
            $cpmkScore        = 0;

            foreach ($cpmk->indicators as $ind) {
                $indWeight   = (float) $ind->percentage / 100;
                $compResults = [];
                $indScore    = 0;

                foreach ($ind->assessments as $asmnt) {
                    $compWeight = (float) $asmnt->percentage / 100;
                    $rawScore   = $scoreMap[$asmnt->id][$sid] ?? null;
                    $weighted   = $rawScore !== null ? round($rawScore * $compWeight, 2) : null;

                    $compResults[] = [
                        'id'       => $asmnt->id,
                        'name'     => $asmnt->name,
                        'weight'   => $asmnt->percentage,
                        'raw'      => $rawScore,
                        'weighted' => $weighted,
                    ];

                    if ($weighted !== null) {
                        $indScore += $weighted;
                    }
                }

                $indScoreRounded = round($indScore, 2);
                $indWeighted     = count($compResults) ? round($indScoreRounded * $indWeight, 2) : null;

                $indicatorResults[] = [
                    'id'          => $ind->id,
                    'description' => $ind->description,
                    'weight'      => $ind->percentage,
                    'components'  => $compResults,
                    'total'       => $indScoreRounded,
                    'weighted'    => $indWeighted,
                ];

                if ($indWeighted !== null) {
                    $cpmkScore += $indWeighted;
                }
            }

            $cpmkScoreRounded = round($cpmkScore, 2);
            $cpmkWeighted     = round($cpmkScoreRounded * $cpmkWeight, 2);
            $cpmkLulus        = GradeService::cpmkLulus($cpmkScoreRounded);

            $cpmkResults[] = [
                'id'         => $cpmk->id,
                'code'       => $cpmk->code,
                'cpl'        => $cpmk->cpl,
                'weight'     => $cpmk->percentage,
                'indicators' => $indicatorResults,
                'total'      => $cpmkScoreRounded,
                'weighted'   => $cpmkWeighted,
                'lulus'      => $cpmkLulus,
            ];

            $cpmkScoresForFinal[] = ['score' => $cpmkScoreRounded, 'weight' => $cpmk->percentage];
        }

        $finalScore   = GradeService::nilaiAkhirMataKuliah($cpmkScoresForFinal);
        $konvensional = GradeService::toKonvensional($finalScore);
        $anyFailed    = collect($cpmkResults)->contains('lulus', false);
        $finalGrade   = $konvensional['huruf'];
        $finalMutu    = $konvensional['mutu'];

        return view('kaprodi.laporan.mahasiswa-kelas', compact(
            'student', 'classroom', 'course', 'cpmkResults', 'finalScore', 'finalGrade', 'finalMutu', 'anyFailed'
        ));
    }

    /* ── Arsip: Index ──────────────────────────────────────────────────── */
    public function arsipIndex(Request $request)
    {
        $data = $this->classroomQuery($request, true);
        return view('kaprodi.arsip.index', $data);
    }

    /* ── CPMK Approval: Index ──────────────────────────────────────────── */
    public function cpmkApprovalIndex(Request $request)
    {
        $auth    = Auth::user();
        $prodiId = $auth->activeProdiId();

        $query = ClassroomCpmk::with(['classroom.course', 'cpl', 'creator', 'indicators'])
            ->orderByRaw("FIELD(status, 'pending', 'draft', 'approved', 'rejected')")
            ->orderByDesc('updated_at');

        // Scope KETAT: hanya CPMK dari kelas dalam prodi kaprodi (tanpa fallback NULL)
        if ($auth->role === 'kaprodi' && $prodiId) {
            $query->whereHas('classroom', function ($cq) use ($prodiId) {
                $cq->whereHas('course', fn($q) =>
                    $q->where('program_studi_id', $prodiId)
                );
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        $cpmks        = $query->get();
        $classrooms   = Classroom::with('course')->orderByDesc('academic_year')->get();
        $pendingCount = ClassroomCpmk::where('status', 'pending')->count();

        return view('kaprodi.cpmk-approvals.index', compact('cpmks', 'classrooms', 'pendingCount'));
    }

    /* ── CPMK Approval: Show ───────────────────────────────────────────── */
    public function cpmkApprovalShow(ClassroomCpmk $classroomCpmk)
    {
        $classroomCpmk->load(['classroom.course', 'cpl', 'creator', 'indicators.assessments']);
        $templates = $classroomCpmk->classroom->course?->cpmks()->with('cpl')->get() ?? collect();
        return view('kaprodi.cpmk-approvals.show', compact('classroomCpmk', 'templates'));
    }

    /* ── CPMK Approve ──────────────────────────────────────────────────── */
    public function cpmkApprove(Request $request, ClassroomCpmk $classroomCpmk)
    {
        abort_unless($classroomCpmk->status === 'pending', 422, 'CPMK tidak dalam status menunggu persetujuan.');

        $classroomCpmk->update([
            'status'         => 'approved',
            'approved_at'    => now(),
            'rejection_note' => null,
        ]);

        $classroomCpmk->creator?->notify(new CpmkStatusChanged($classroomCpmk, 'approved'));

        return redirect()
            ->route('kaprodi.cpmk-approvals.index')
            ->with('success', "CPMK {$classroomCpmk->code} telah disetujui.");
    }

    /* ── CPMK Reject ───────────────────────────────────────────────────── */
    public function cpmkReject(Request $request, ClassroomCpmk $classroomCpmk)
    {
        $request->validate([
            'rejection_note' => 'required|string|min:5|max:1000',
        ]);

        abort_unless($classroomCpmk->status === 'pending', 422, 'CPMK tidak dalam status menunggu persetujuan.');

        $classroomCpmk->update([
            'status'         => 'rejected',
            'rejection_note' => $request->rejection_note,
            'approved_at'    => null,
        ]);

        $classroomCpmk->creator?->notify(new CpmkStatusChanged($classroomCpmk, 'rejected'));

        return redirect()
            ->route('kaprodi.cpmk-approvals.index')
            ->with('success', "CPMK {$classroomCpmk->code} telah ditolak dengan catatan revisi.");
    }

    /* ── Shared: Build rows for report ─────────────────────────────────── */
    private function buildRows($students, $cpmks, array $scoreMap): array
    {
        $rows = [];

        foreach ($students as $student) {
            $sid         = $student->id;
            $cpmkResults = [];
            $cpmkScoresForFinal = [];

            foreach ($cpmks as $cpmk) {
                $cpmkWeight       = (float) $cpmk->percentage / 100;
                $indicatorResults = [];
                $cpmkScore        = 0;

                foreach ($cpmk->indicators as $ind) {
                    $indWeight   = (float) $ind->percentage / 100;
                    $compResults = [];
                    $indScore    = 0;

                    foreach ($ind->assessments as $asmnt) {
                        $compWeight = (float) $asmnt->percentage / 100;
                        $rawScore   = $scoreMap[$asmnt->id][$sid] ?? null;
                        $weighted   = $rawScore !== null ? round($rawScore * $compWeight, 2) : null;

                        $compResults[] = [
                            'id'       => $asmnt->id,
                            'name'     => $asmnt->name,
                            'weight'   => $asmnt->percentage,
                            'raw'      => $rawScore,
                            'weighted' => $weighted,
                        ];

                        if ($weighted !== null) {
                            $indScore += $weighted;
                        }
                    }

                    $indScoreRounded = round($indScore, 2);
                    $indWeighted     = count($compResults) ? round($indScoreRounded * $indWeight, 2) : null;

                    $indicatorResults[] = [
                        'id'          => $ind->id,
                        'description' => $ind->description,
                        'weight'      => $ind->percentage,
                        'components'  => $compResults,
                        'total'       => $indScoreRounded,
                        'weighted'    => $indWeighted,
                    ];

                    if ($indWeighted !== null) {
                        $cpmkScore += $indWeighted;
                    }
                }

                $cpmkScoreRounded = round($cpmkScore, 2);
                $cpmkWeighted     = round($cpmkScoreRounded * $cpmkWeight, 2);
                $cpmkLulus        = GradeService::cpmkLulus($cpmkScoreRounded);

                $cpmkResults[] = [
                    'id'         => $cpmk->id,
                    'code'       => $cpmk->code,
                    'weight'     => $cpmk->percentage,
                    'indicators' => $indicatorResults,
                    'total'      => $cpmkScoreRounded,
                    'weighted'   => $cpmkWeighted,
                    'lulus'      => $cpmkLulus,
                ];

                $cpmkScoresForFinal[] = ['score' => $cpmkScoreRounded, 'weight' => $cpmk->percentage];
            }

            $finalScore   = GradeService::nilaiAkhirMataKuliah($cpmkScoresForFinal);
            $konvensional = GradeService::toKonvensional($finalScore);
            $anyFailed    = collect($cpmkResults)->contains('lulus', false);

            $rows[] = [
                'student'     => $student,
                'cpmks'       => $cpmkResults,
                'final_score' => $finalScore,
                'final_grade' => $konvensional['huruf'],
                'final_mutu'  => $konvensional['mutu'],
                'any_failed'  => $anyFailed,
            ];
        }

        return $rows;
    }
}