<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomCpmkAssessment;
use App\Models\ClassroomCpmkAssessmentScore;
use App\Models\ClassroomCpmkIndicator;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
 
        $classrooms = Classroom::with([
            'course',
            'cpmks' => function ($q) use ($user) {
                $q->wherePivot('lecturer_id', $user->id)->orderBy('obe_cpmk.id');
            },
        ])
            ->where('is_archived', false)
            ->where(function ($q) use ($user) {
                // Kondisi 1: dosen ditugaskan di pivot CPMK kelas
                $q->whereHas('cpmks', function ($qq) use ($user) {
                    $qq->where('obe_kelas_cpmk_dosen.lecturer_id', $user->id);
                })
                // Kondisi 2: dosen adalah PIC utama kelas (kolom lecturer_id)
                ->orWhere('lecturer_id', $user->id);
            })
            ->orderByDesc('academic_year')
            ->orderBy('period_type')
            ->orderBy('name')
            ->get();
 
        $activePeriod = Classroom::currentPeriod();
 
        return view('dosen.dashboard', compact('classrooms', 'activePeriod'));
    }

    /**
     * Pemetaan CPL → CPMK khusus untuk dosen yang login.
     * Hanya menampilkan mata kuliah yang ditugaskan ke dosen ini
     * (via pivot obe_kelas_cpmk_dosen atau sebagai lecturer_id kelas aktif).
     */
    public function pemetaan()
    {
        $user = Auth::user();

        // Ambil course_id dari kelas aktif yang ditugaskan ke dosen ini
        $courseIds = \App\Models\Classroom::where('is_archived', false)
            ->where(function ($q) use ($user) {
                $q->whereHas('cpmks', fn($qq) =>
                    $qq->where('obe_kelas_cpmk_dosen.lecturer_id', $user->id)
                )
                ->orWhere('lecturer_id', $user->id);
            })
            ->pluck('course_id')
            ->unique();

        // Load course beserta CPMK & CPL — hanya mata kuliah yang ditugaskan
        $courses = \App\Models\Course::with(['cpmks.cpl'])
            ->whereIn('id', $courseIds)
            ->orderBy('semester')
            ->orderBy('code')
            ->get();

        // Tampilkan SEMUA CPL (bukan hanya yang terpilih di MK ini)
        $cpls = \App\Models\Cpl::orderBy('code')->get();

        return view('dosen.pemetaan', compact('courses', 'cpls'));
    }

    public function riwayat()
    {
        $user = Auth::user();
 
        $classrooms = Classroom::with([
            'course',
            'cpmks' => function ($q) use ($user) {
                $q->wherePivot('lecturer_id', $user->id)->orderBy('obe_cpmk.id');
            },
        ])
            ->where('is_archived', true)
            ->where(function ($q) use ($user) {
                $q->whereHas('cpmks', function ($qq) use ($user) {
                    $qq->where('obe_kelas_cpmk_dosen.lecturer_id', $user->id);
                })
                ->orWhere('lecturer_id', $user->id);
            })
            ->orderByDesc('academic_year')
            ->orderBy('period_type')
            ->orderBy('name')
            ->get();
 
        $activePeriod = Classroom::currentPeriod();
 
        return view('dosen.riwayat', compact('classrooms', 'activePeriod'));
    }

    public function show(Classroom $classroom)
    {
        $user = Auth::user();
        $course = $classroom->course;
        $course?->load(['cpls', 'prerequisite']);

        // Hanya CPMK yang ditugaskan ke dosen yang sedang login pada kelas ini.
        // Komponen penilaian (assessments) difilter by classroom_id
        // agar tiap dosen di tiap kelas punya komponen sendiri.
        $cpmks = $classroom->cpmks()
            ->wherePivot('lecturer_id', $user->id)
            ->with([
                'cpl',
                'indicators' => fn($q) => $q->orderBy('id'),
                'indicators.assessments' => fn($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
            ])
            ->orderBy('obe_cpmk.id')
            ->get();

        return view('dosen.courses.show', compact('course', 'classroom', 'cpmks'));
    }

    /**
     * Halaman Laporan Nilai per Kelas — menggunakan CPMK dari mata kuliah (kaprodi-defined)
     */
    public function report(Classroom $classroom)
    {
        $course = $classroom->course;

        // Load CPMKs dari mata kuliah beserta indikator & komponen per kelas ini
        $cpmks = $course
            ? $course->cpmks()->with([
                'cpl',
                'indicators' => fn($q) => $q->orderBy('id'),
                'indicators.assessments' => fn($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'indicators.assessments.scores',
            ])->orderBy('id')->get()
            : collect();

        // Mahasiswa yang enrolled di kelas ini
        $students = $classroom->students()->orderBy('identity')->get();

        // Build scoreMap: [assessment_id][student_id] => score
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

        $rows = $this->buildRows($students, $cpmks, $scoreMap);
        $cplRows = \App\Services\CplAchievementService::perClassroom($rows, $cpmks);

        return view('dosen.classrooms.report', compact('classroom', 'course', 'rows', 'cpmks', 'cplRows'));
    }

    /* ─── Score management ─────────────────────────────── */

    public function scoresIndex(ClassroomCpmkAssessment $assessment)
    {
        $assessment->load(['indicator.cpmk.classroom.students', 'scores']);

        $classroom = $assessment->indicator->cpmk->classroom;
        $students = $classroom->students->sortBy('identity');

        $scoreMap = $assessment->scores->keyBy('student_id');

        return view('dosen.assessments.scores', compact('assessment', 'classroom', 'students', 'scoreMap'));
    }

    public function storeScores(Request $request, ClassroomCpmkAssessment $assessment)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|exists:users,id',
            'scores.*.score' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['scores'] as $item) {
                ClassroomCpmkAssessmentScore::updateOrCreate(
                    [
                        'classroom_cpmk_assessment_id' => $assessment->id,
                        'student_id' => $item['student_id'],
                    ],
                    ['score' => $item['score']]
                );
            }
            DB::commit();

            return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan nilai: '.$e->getMessage());
        }
    }

    /* ─── Indicator management ─────────────────────────── */

    public function editIndicator(ClassroomCpmkIndicator $indicator)
    {
        $indicator->load('assessments');

        return view('dosen.indicators.edit', compact('indicator'));
    }

    public function storeComponents(Request $request, ClassroomCpmkIndicator $indicator)
    {
        $validated = $request->validate([
            'components' => 'required|array|min:1',
            'components.*.nama' => 'required|string|max:255',
            'components.*.deskripsi' => 'nullable|string',
            'components.*.bobotType' => 'required|in:otomatis,manual',
            'components.*.bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $indicator->assessments()->delete();

            foreach ($validated['components'] as $comp) {
                $isAuto = $comp['bobotType'] === 'otomatis';
                $indicator->assessments()->create([
                    'name' => $comp['nama'],
                    'description' => $comp['deskripsi'] ?? null,
                    'percentage' => $isAuto ? 0 : (float) $comp['bobot'],
                    'is_auto' => $isAuto,
                ]);
            }

            // Recalculate auto weights
            $assessments = $indicator->assessments()->orderBy('id')->get();
            $manualTotal = $assessments->where('is_auto', false)->sum('percentage');
            $autoItems = $assessments->where('is_auto', true);
            $autoCount = $autoItems->count();

            if ($autoCount > 0) {
                $remaining = max(0, 100 - $manualTotal);
                $base = floor(($remaining / $autoCount) * 100) / 100;
                $remainder = round($remaining - ($base * $autoCount), 2);
                $i = 0;
                foreach ($autoItems as $a) {
                    $a->update(['percentage' => $base + ($i === $autoCount - 1 ? $remainder : 0)]);
                    $i++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Komponen penilaian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    /**
     * Simpan komponen penilaian untuk Indicator lama (CPMK dari kaprodi → Assessment model)
     */
    public function storeIndicatorComponents(Request $request, \App\Models\Indicator $indicator)
    {
        $validated = $request->validate([
            'classroom_id' => 'required|exists:obe_kelas,id',
            'components'   => 'required|array|min:1',
            'components.*.nama'      => 'required|string|max:255',
            'components.*.deskripsi' => 'nullable|string',
            'components.*.bobotType' => 'required|in:otomatis,manual',
            'components.*.bobot'     => 'nullable|numeric|min:0|max:100',
        ]);

        $classroomId = (int) $validated['classroom_id'];

        DB::beginTransaction();
        try {
            // Hapus hanya komponen untuk kelas ini — kelas lain tidak terpengaruh
            $indicator->assessments()->where('classroom_id', $classroomId)->delete();

            foreach ($validated['components'] as $comp) {
                $isAuto = $comp['bobotType'] === 'otomatis';
                $indicator->assessments()->create([
                    'classroom_id' => $classroomId,
                    'name'         => $comp['nama'],
                    'description'  => $comp['deskripsi'] ?? null,
                    'percentage'   => $isAuto ? 0 : (float) $comp['bobot'],
                    'is_auto'      => $isAuto,
                ]);
            }

            // Recalculate auto weights
            $assessments = $indicator->assessments()->orderBy('id')->get();
            $manualTotal = $assessments->where('is_auto', false)->sum('percentage');
            $autoItems = $assessments->where('is_auto', true);
            $autoCount = $autoItems->count();

            if ($autoCount > 0) {
                $remaining = max(0, 100 - $manualTotal);
                $base = floor(($remaining / $autoCount) * 100) / 100;
                $remainder = round($remaining - ($base * $autoCount), 2);
                $i = 0;
                foreach ($autoItems as $a) {
                    $a->update(['percentage' => $base + ($i === $autoCount - 1 ? $remainder : 0)]);
                    $i++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Komponen penilaian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    /* ─── Shared: Build rows for report ──────────────────── */
    private function buildRows($students, $cpmks, array $scoreMap): array
    {
        $rows = [];

        foreach ($students as $student) {
            $sid = $student->id;
            $cpmkResults = [];
            $cpmkScoresForFinal = [];

            foreach ($cpmks as $cpmk) {
                $cpmkWeight = (float) $cpmk->percentage / 100;
                $indicatorResults = [];
                $cpmkScore = 0;

                foreach ($cpmk->indicators as $ind) {
                    $indWeight = (float) $ind->percentage / 100;
                    $compResults = [];
                    $indScore = 0;

                    foreach ($ind->assessments as $asmnt) {
                        $compWeight = (float) $asmnt->percentage / 100;
                        $rawScore = $scoreMap[$asmnt->id][$sid] ?? null;
                        $weighted = $rawScore !== null ? round($rawScore * $compWeight, 2) : null;

                        $compResults[] = [
                            'id' => $asmnt->id,
                            'name' => $asmnt->name,
                            'weight' => $asmnt->percentage,
                            'raw' => $rawScore,
                            'weighted' => $weighted,
                        ];

                        if ($weighted !== null) {
                            $indScore += $weighted;
                        }
                    }

                    $indScoreRounded = round($indScore, 2);
                    $indWeighted = count($compResults) ? round($indScoreRounded * $indWeight, 2) : null;

                    $indicatorResults[] = [
                        'id' => $ind->id,
                        'description' => $ind->description,
                        'weight' => $ind->percentage,
                        'components' => $compResults,
                        'total' => $indScoreRounded,
                        'weighted' => $indWeighted,
                    ];

                    if ($indWeighted !== null) {
                        $cpmkScore += $indWeighted;
                    }
                }

                $cpmkScoreRounded = round($cpmkScore, 2);
                $cpmkWeighted = round($cpmkScoreRounded * $cpmkWeight, 2);
                $cpmkLulus = GradeService::cpmkLulus($cpmkScoreRounded);

                $cpmkResults[] = [
                    'id' => $cpmk->id,
                    'code' => $cpmk->code,
                    'weight' => $cpmk->percentage,
                    'indicators' => $indicatorResults,
                    'total' => $cpmkScoreRounded,
                    'weighted' => $cpmkWeighted,
                    'lulus' => $cpmkLulus,
                ];

                $cpmkScoresForFinal[] = ['score' => $cpmkScoreRounded, 'weight' => $cpmk->percentage];
            }

            $finalScore = GradeService::nilaiAkhirMataKuliah($cpmkScoresForFinal);
            $konvensional = GradeService::toKonvensional($finalScore);
            $anyFailed = collect($cpmkResults)->contains('lulus', false);

            $rows[] = [
                'student' => $student,
                'cpmks' => $cpmkResults,
                'final_score' => $finalScore,
                'final_grade' => $konvensional['huruf'],
                'final_mutu' => $konvensional['mutu'],
                'any_failed' => $anyFailed,
            ];
        }

        return $rows;
    }

    /**
     * Export rekap nilai mahasiswa ke format Excel SATU UNRI.
     * Menyimpan bobot (Partisipasi Aktif, Presensi, Kuis, UTS, Proyek, Tugas, Praktikum, UAS)
     * pada classroom, lalu mengunduh file .xlsx kosong yang siap diisi nilai komponen.
     */
    public function exportSatuUnri(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'partisipasi_aktif' => 'nullable|numeric|min:0|max:100',
            'presensi' => 'nullable|numeric|min:0|max:100',
            'kuis' => 'nullable|numeric|min:0|max:100',
            'uts' => 'nullable|numeric|min:0|max:100',
            'proyek' => 'nullable|numeric|min:0|max:100',
            'tugas' => 'nullable|numeric|min:0|max:100',
            'praktikum' => 'nullable|numeric|min:0|max:100',
            'uas' => 'nullable|numeric|min:0|max:100',
        ]);

        $bobot = [
            'partisipasi_aktif' => (float) ($validated['partisipasi_aktif'] ?? 0),
            'presensi' => (float) ($validated['presensi'] ?? 0),
            'kuis' => (float) ($validated['kuis'] ?? 0),
            'uts' => (float) ($validated['uts'] ?? 0),
            'proyek' => (float) ($validated['proyek'] ?? 0),
            'tugas' => (float) ($validated['tugas'] ?? 0),
            'praktikum' => (float) ($validated['praktikum'] ?? 0),
            'uas' => (float) ($validated['uas'] ?? 0),
        ];

        if (round(array_sum($bobot), 2) != 100.0) {
            return back()->with('error', 'Total bobot harus 100% (saat ini: '.array_sum($bobot).'%).');
        }

        $classroom->update(['satu_unri_bobot' => $bobot]);

        $course = $classroom->course;
        $students = $classroom->students()->orderBy('identity')->get();

        // Build computed scores (nilai konversi) untuk setiap mahasiswa
        // Komponen penilaian DIFILTER per kelas ini agar tidak tercampur kelas lain.
        $cpmks = $course
            ? $course->cpmks()->with([
                'cpl',
                'indicators' => fn($q) => $q->orderBy('id'),
                'indicators.assessments' => fn($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'indicators.assessments.scores',
            ])->orderBy('id')->get()
            : collect();

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
        $rows = $this->buildRows($students, $cpmks, $scoreMap);
        $rowsByStudentId = collect($rows)->keyBy(fn ($r) => $r['student']->id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        // Header informasi (baris 1–3)
        $sheet->setCellValue('A1', 'SEMESTER : '.ucfirst($classroom->period_type ?? '-').' '.($classroom->academic_year ?? '-'));
        $sheet->setCellValue('A2', 'Matakuliah : '.($course?->name ?? '-').($course?->code ? ' ('.$course->code.')' : ''));
        $sheet->setCellValue('A3', 'Kelas : '.$classroom->name);

        foreach (['A1', 'A2', 'A3'] as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Header kolom (baris 6) — kolom A dibiarkan kosong sebagai padding sempit,
        // data mulai dari kolom B.
        $headers = ['', 'NO', 'NIM', 'NAMA', 'KE', 'Partisipasi Aktif', 'Proyek', 'Presensi', 'Tugas', 'Quiz', 'Praktikum', 'UTS', 'UAS', 'NILAI AKHIR', 'MUTU', 'GRADE'];
        $col = 'A';
        foreach ($headers as $h) {
            if ($h !== '') {
                $sheet->setCellValue($col.'6', $h);
                $sheet->getStyle($col.'6')->getFont()->setBold(true);
            }
            $col++;
        }

        // Baris bobot (baris 5) — disimpan sebagai numeric (persen) agar formula
        // pada kolom NILAI AKHIR dapat menghitung.
        $bobotRow = ['', '', '', '', '', $bobot['partisipasi_aktif'], $bobot['proyek'], $bobot['presensi'], $bobot['tugas'], $bobot['kuis'], $bobot['praktikum'], $bobot['uts'], $bobot['uas']];
        $col = 'A';
        foreach ($bobotRow as $v) {
            if ($v !== '') {
                $sheet->setCellValue($col.'5', $v);
                $sheet->getStyle($col.'5')->getNumberFormat()->setFormatCode('0"%"');
                $sheet->getStyle($col.'5')->getFont()->setItalic(true);
            }
            $col++;
        }
        $sheet->setCellValue('E5', 'Bobot:');
        $sheet->getStyle('E5')->getFont()->setBold(true);

        // Data mahasiswa mulai baris 7. Tiap komponen F..M diisi kontribusi tertimbang
        // (= nilai_akhir × bobot%/100), dan NILAI AKHIR = SUM(F:M).
        $bobotByCol = [
            'F' => $bobot['partisipasi_aktif'],
            'G' => $bobot['proyek'],
            'H' => $bobot['presensi'],
            'I' => $bobot['tugas'],
            'J' => $bobot['kuis'],
            'K' => $bobot['praktikum'],
            'L' => $bobot['uts'],
            'M' => $bobot['uas'],
        ];

        $rowNum = 7;
        foreach ($students as $idx => $student) {
            $sheet->setCellValue('B'.$rowNum, $idx + 1);
            $sheet->setCellValue('C'.$rowNum, (string) $student->identity);
            $sheet->setCellValue('D'.$rowNum, $student->name);
            $sheet->setCellValue('E'.$rowNum, 1);

            $studentRow = $rowsByStudentId->get($student->id);
            if ($studentRow) {
                $finalScore = $studentRow['any_failed'] ? 0 : $studentRow['final_score'];
                $finalScoreRounded = round($finalScore, 2);

                foreach ($bobotByCol as $compCol => $weight) {
                    $sheet->setCellValue($compCol.$rowNum, round($finalScoreRounded * $weight / 100, 2));
                }

                $sheet->setCellValue('N'.$rowNum, $finalScoreRounded);
                $sheet->setCellValue('O'.$rowNum, round($studentRow['final_mutu'], 2));
                $sheet->setCellValue('P'.$rowNum, $studentRow['final_grade']);

                $sheet->getStyle('N'.$rowNum)->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle('P'.$rowNum)->getFont()->setBold(true);
                if ($studentRow['any_failed'] || $studentRow['final_grade'] === 'E') {
                    $sheet->getStyle('N'.$rowNum.':P'.$rowNum)
                        ->getFont()->getColor()->setRGB('B91C1C');
                }
            }
            $rowNum++;
        }

        $sheet->getStyle('N6:P6')->getFont()->setBold(true);
        $sheet->getStyle('N6:P6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFF7CC');

        // Kolom A sebagai padding sempit; sisanya auto-size.
        $sheet->getColumnDimension('A')->setWidth(2);
        foreach (range('B', 'P') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $clean = fn (string $s): string => trim(preg_replace('/\s+/', ' ', preg_replace('/[\\\\\/:*?"<>|]/', '', $s)));
        $kelas = $clean($classroom->name ?? '-');
        $matkul = $clean($course?->name ?? '-');
        $periode = $clean(ucfirst($classroom->period_type ?? '-').' '.($classroom->academic_year ?? '-'));
        $filename = "Rekap Nilai - {$kelas} - {$matkul} - {$periode} - ".date('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}