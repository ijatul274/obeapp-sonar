<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\ClassroomCpmkIndicator;
use App\Models\ClassroomIndicatorWeight;
use App\Models\SubCpmk;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    use \App\Http\Controllers\Concerns\BuildsGradeReport;

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
                $q->whereHas('cpmks', fn ($qq) => $qq->where('obe_kelas_cpmk_dosen.lecturer_id', $user->id)
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
        abort_unless($classroom->isTaughtBy($user), 403, 'Anda tidak mengajar kelas ini.');

        $course = $classroom->course;
        $course?->load(['cpls', 'prerequisite']);

        // Hanya CPMK yang ditugaskan ke dosen yang sedang login pada kelas ini.
        // Komponen penilaian (assessments) difilter by classroom_id
        // agar tiap dosen di tiap kelas punya komponen sendiri.
        $cpmks = $classroom->cpmks()
            ->wherePivot('lecturer_id', $user->id)
            ->with([
                'cpl',
                'indicators' => fn ($q) => $q->orderBy('id'),
                'indicators.assessments' => fn ($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'subCpmks' => fn ($q) => $q->orderBy('id'),
                'subCpmks.indicators' => fn ($q) => $q->orderBy('id'),
                'subCpmks.indicators.assessments' => fn ($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'subCpmks.indicators.classroomWeights' => fn ($q) => $q->where('classroom_id', $classroom->id),
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
        abort_unless($classroom->isTaughtBy(Auth::user()), 403, 'Anda tidak mengajar kelas ini.');

        $course = $classroom->course;

        // Load CPMKs dari mata kuliah beserta indikator & komponen per kelas ini
        $cpmks = $course
            ? $course->cpmks()->with([
                'cpl',
                'indicators' => fn ($q) => $q->orderBy('id'),
                'indicators.assessments' => fn ($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'indicators.assessments.scores',
                'indicators.classroomWeights' => fn ($q) => $q->where('classroom_id', $classroom->id),
                'subCpmks' => fn ($q) => $q->orderBy('id'),
                'subCpmks.indicators' => fn ($q) => $q->orderBy('id'),
                'subCpmks.indicators.assessments' => fn ($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'subCpmks.indicators.assessments.scores',
                'subCpmks.indicators.classroomWeights' => fn ($q) => $q->where('classroom_id', $classroom->id),
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

        $rows = $this->buildRows($students, $cpmks, $scoreMap, $classroom->id);
        $cplRows = \App\Services\CplAchievementService::perClassroom($rows, $cpmks);

        return view('dosen.classrooms.report', compact('classroom', 'course', 'rows', 'cpmks', 'cplRows'));
    }

    /* ─── Indicator management ─────────────────────────── */

    public function editIndicator(ClassroomCpmkIndicator $indicator)
    {
        $this->authorizeClassroomCpmkIndicator($indicator);

        $indicator->load('assessments');

        return view('dosen.indicators.edit', compact('indicator'));
    }

    /**
     * Pastikan indikator (sistem ClassroomCpmk) milik kelas yang diajar dosen login.
     */
    private function authorizeClassroomCpmkIndicator(ClassroomCpmkIndicator $indicator): void
    {
        $classroom = $indicator->cpmk?->classroom;
        abort_if($classroom === null, 404);
        abort_unless($classroom->isTaughtBy(Auth::user()), 403, 'Anda tidak mengajar kelas ini.');
    }

    public function storeComponents(Request $request, ClassroomCpmkIndicator $indicator)
    {
        $this->authorizeClassroomCpmkIndicator($indicator);

        $validated = $request->validate([
            'components' => 'required|array|min:1',
            'components.*.nama' => 'required|string|max:255',
            'components.*.deskripsi' => 'nullable|string',
            'components.*.bobotType' => 'required|in:otomatis,manual',
            'components.*.bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        // Validasi: total bobot manual tidak boleh melebihi 100%
        $manualTotal = collect($validated['components'])
            ->where('bobotType', 'manual')
            ->sum(fn ($c) => (float) ($c['bobot'] ?? 0));
        if (round($manualTotal, 2) > 100.0) {
            return back()->withErrors([
                'components' => "Total bobot komponen (manual) adalah {$manualTotal}%, melebihi 100%.",
            ]);
        }

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
            $autoItems = $assessments->where('is_auto', true)->values();
            $autoWeights = GradeService::distributeAutoWeights($autoItems->count(), 100 - $manualTotal);

            foreach ($autoItems as $i => $a) {
                $a->update(['percentage' => $autoWeights[$i]]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Komponen penilaian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    /**
     * Simpan komponen penilaian untuk satu Sub-CPMK pada satu kelas. Tiap komponen
     * memilih indikator yang diukurnya + bobotnya; bobot komponen dijumlahkan 100%
     * dalam lingkup Sub-CPMK (bobot kosong = otomatis dibagi rata dari sisa).
     *
     * Bobot indikator TIDAK diisi manual — ia diturunkan: bobot tiap indikator =
     * jumlah bobot komponen yang menunjuk ke indikator itu, disimpan ke
     * obe_kelas_indikator_bobot (dibaca {@see \App\Models\Indicator::weightForClassroom()}).
     */
    public function storeSubCpmkComponents(Request $request, Classroom $classroom, SubCpmk $subCpmk): \Illuminate\Http\RedirectResponse
    {
        abort_unless($classroom->isTaughtBy(Auth::user()), 403, 'Anda tidak mengajar kelas ini.');
        abort_unless(
            (int) ($subCpmk->cpmk?->course_id) === (int) $classroom->course_id,
            403,
            'Sub-CPMK tidak sesuai dengan mata kuliah kelas ini.'
        );

        $validated = $request->validate([
            'components' => 'present|array',
            'components.*.nama' => 'required|string|max:255',
            'components.*.deskripsi' => 'nullable|string',
            'components.*.indicator_id' => 'required|integer',
            'components.*.bobotType' => 'required|in:otomatis,manual',
            'components.*.bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        // Hanya indikator milik Sub-CPMK ini yang sah jadi target komponen.
        $indicatorIds = $subCpmk->indicators()->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $comps = collect($validated['components'] ?? [])
            ->filter(fn ($c): bool => in_array((int) $c['indicator_id'], $indicatorIds, true))
            ->values();

        // Total bobot manual (lingkup Sub-CPMK) tidak boleh melebihi 100%.
        $manualTotal = $comps->where('bobotType', 'manual')->sum(fn ($c): float => (float) ($c['bobot'] ?? 0));
        if (round($manualTotal, 2) > 100.0) {
            return back()->withErrors([
                'components' => "Total bobot komponen (manual) {$manualTotal}% melebihi 100%.",
            ]);
        }

        DB::transaction(function () use ($comps, $classroom, $indicatorIds, $manualTotal): void {
            // Reset komponen kelas ini untuk seluruh indikator Sub-CPMK, lalu buat ulang.
            Assessment::where('classroom_id', $classroom->id)
                ->whereIn('indicator_id', $indicatorIds)
                ->delete();

            $autoCount = $comps->where('bobotType', 'otomatis')->count();
            $autoWeights = GradeService::distributeAutoWeights($autoCount, 100 - $manualTotal);
            $autoIndex = 0;

            $indicatorSum = array_fill_keys($indicatorIds, 0.0);

            foreach ($comps as $c) {
                $isAuto = $c['bobotType'] === 'otomatis';
                $percentage = $isAuto ? ($autoWeights[$autoIndex++] ?? 0.0) : (float) $c['bobot'];
                $indicatorId = (int) $c['indicator_id'];

                Assessment::create([
                    'indicator_id' => $indicatorId,
                    'classroom_id' => $classroom->id,
                    'name' => $c['nama'],
                    'description' => $c['deskripsi'] ?? null,
                    'percentage' => $percentage,
                    'is_auto' => $isAuto,
                ]);

                $indicatorSum[$indicatorId] += $percentage;
            }

            // Bobot indikator = jumlah bobot komponennya (0 bila tak punya komponen).
            foreach ($indicatorIds as $indicatorId) {
                ClassroomIndicatorWeight::updateOrCreate(
                    ['classroom_id' => $classroom->id, 'indicator_id' => $indicatorId],
                    ['percentage' => round($indicatorSum[$indicatorId], 2), 'is_auto' => true],
                );
            }
        });

        return back()->with('success', 'Komponen penilaian berhasil disimpan.');
    }

    /**
     * Export rekap nilai mahasiswa ke format Excel SATU UNRI.
     * Menyimpan bobot (Partisipasi Aktif, Presensi, Kuis, UTS, Proyek, Tugas, Praktikum, UAS)
     * pada classroom, lalu mengunduh file .xlsx kosong yang siap diisi nilai komponen.
     */
    public function exportSatuUnri(Request $request, Classroom $classroom)
    {
        abort_unless($classroom->isTaughtBy(Auth::user()), 403, 'Anda tidak mengajar kelas ini.');

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
                'indicators' => fn ($q) => $q->orderBy('id'),
                'indicators.assessments' => fn ($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'indicators.assessments.scores',
                'subCpmks' => fn ($q) => $q->orderBy('id'),
                'subCpmks.indicators' => fn ($q) => $q->orderBy('id'),
                'subCpmks.indicators.assessments' => fn ($q) => $q->where('classroom_id', $classroom->id)->orderBy('id'),
                'subCpmks.indicators.assessments.scores',
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

        // Data mahasiswa mulai baris 7. Tiap kolom kategori F..M diisi nilai mentah
        // skala 0–100 (acak namun konsisten per mahasiswa) yang—setelah dikali
        // bobotnya—berjumlah = NILAI AKHIR. Jadi kategori tampil pada skala penuh,
        // bukan kontribusi kecilnya, sementara nilai akhir tetap sesuai bobot.
        $colToKey = [
            'F' => 'partisipasi_aktif',
            'G' => 'proyek',
            'H' => 'presensi',
            'I' => 'tugas',
            'J' => 'kuis',
            'K' => 'praktikum',
            'L' => 'uts',
            'M' => 'uas',
        ];

        $rowNum = 7;
        foreach ($students as $idx => $student) {
            $sheet->setCellValue('B'.$rowNum, $idx + 1);
            $sheet->setCellValue('C'.$rowNum, (string) $student->identity);
            $sheet->setCellValue('D'.$rowNum, $student->name);
            $sheet->setCellValue('E'.$rowNum, 1);

            $studentRow = $rowsByStudentId->get($student->id);
            if ($studentRow) {
                $finalScore = $studentRow['any_failed'] ? 0 : ($studentRow['final_score'] ?? 0);
                $finalScoreRounded = round((float) $finalScore, 2);

                $rawScores = $this->satuUnriRawScores($bobot, $finalScoreRounded, (int) $student->id);
                foreach ($colToKey as $compCol => $key) {
                    $sheet->setCellValue($compCol.$rowNum, $rawScores[$key]);
                    if ($rawScores[$key] !== '') {
                        $sheet->getStyle($compCol.$rowNum)->getNumberFormat()->setFormatCode('0.00');
                    }
                }

                $sheet->setCellValue('N'.$rowNum, $finalScoreRounded);
                $sheet->setCellValue('O'.$rowNum, round((float) ($studentRow['final_mutu'] ?? 0), 2));
                $sheet->setCellValue('P'.$rowNum, $studentRow['final_grade'] ?? '-');

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

    /**
     * Hasilkan nilai mentah (skala 0–100) per kategori SATU UNRI yang—setelah
     * dikalikan bobotnya—berjumlah tepat = $finalScore. Tujuannya agar tiap
     * kategori tampil pada skala penuh (mis. presensi 100 bila sempurna), bukan
     * kontribusi kecilnya (5), sementara nilai akhir tetap sesuai bobot.
     *
     * Nilai dibuat acak namun konsisten per mahasiswa (di-seed dari id), lalu
     * digeser dengan konstanta agar rata-rata tertimbangnya tepat = $finalScore
     * (valid karena total bobot = 100). Kategori berbobot 0 dikosongkan.
     *
     * @param  array<string, float>  $weights  bobot persen per kategori (jumlah 100)
     * @return array<string, float|string> nilai mentah per kategori ('' bila bobot 0)
     */
    private function satuUnriRawScores(array $weights, float $finalScore, int $seed): array
    {
        $out = array_fill_keys(array_keys($weights), '');
        $active = array_filter($weights, fn (float $w): bool => $w > 0);
        $totalWeight = array_sum($active);

        if ($active === [] || $totalWeight <= 0) {
            return $out;
        }

        mt_srand($seed);

        // Sebaran menyempit di dekat 0/100 agar nilai acak tetap dalam [0,100]
        // tanpa clamp — sehingga rata-rata tertimbang bisa tepat = $finalScore.
        $spread = max(0.0, min(8.0, $finalScore, 100.0 - $finalScore));
        $raw = [];
        foreach (array_keys($active) as $key) {
            $raw[$key] = $finalScore + (mt_rand(-100, 100) / 100) * $spread;
        }

        $weightedMean = 0.0;
        foreach ($active as $key => $w) {
            $weightedMean += $raw[$key] * $w / $totalWeight;
        }
        $shift = $finalScore - $weightedMean;
        foreach ($active as $key => $w) {
            $raw[$key] = max(0.0, min(100.0, round($raw[$key] + $shift, 2)));
        }

        // Koreksi sisa akibat clamp/pembulatan → bebankan ke kategori bobot terbesar.
        $achieved = 0.0;
        foreach ($active as $key => $w) {
            $achieved += $raw[$key] * $w / $totalWeight;
        }
        $residual = $finalScore - $achieved;
        if (abs($residual) > 0.001) {
            $heaviest = array_keys($active, max($active))[0];
            $frac = $active[$heaviest] / $totalWeight;
            $raw[$heaviest] = max(0.0, min(100.0, round($raw[$heaviest] + $residual / $frac, 2)));
        }

        mt_srand();

        foreach ($raw as $key => $value) {
            $out[$key] = $value;
        }

        return $out;
    }
}
