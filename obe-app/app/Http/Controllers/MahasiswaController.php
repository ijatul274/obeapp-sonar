<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomCpmk;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Hanya kelas aktif (belum diarsipkan)
        $classrooms = $user->classrooms()
            ->with('course')
            ->where('is_archived', false)
            ->orderByDesc('academic_year')
            ->orderBy('name')
            ->get();

        return view('mahasiswa.dashboard', compact('classrooms'));
    }

    public function riwayatKelas()
    {
        $user = Auth::user();

        // Hanya kelas yang sudah diarsipkan
        $classrooms = $user->classrooms()
            ->with('course')
            ->where('is_archived', true)
            ->orderByDesc('academic_year')
            ->orderBy('name')
            ->get();

        return view('mahasiswa.riwayat-kelas', compact('classrooms'));
    }

    public function show(Classroom $classroom)
    {
        $user = Auth::user();

        abort_unless($classroom->students()->where('user_id', $user->id)->exists(), 403);

        $course = $classroom->course;

        // Pakai CPMK template dari mata kuliah (sumber sama dengan dosen report — tabel obe_cpmk).
        $cpmks = $course
            ? $course->cpmks()->with(['cpl', 'indicators.assessments.scores'])->orderBy('id')->get()
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

        // Hitung nilai untuk mahasiswa ini
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
                    $rawScore   = $scoreMap[$asmnt->id][$user->id] ?? null;
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

        return view('mahasiswa.classrooms.show', compact(
            'classroom', 'course', 'cpmkResults', 'finalScore', 'finalGrade', 'finalMutu', 'anyFailed'
        ));
    }

    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'enrollment_code' => 'required|string|size:8',
        ]);

        $classroom = Classroom::where('enrollment_code', $validated['enrollment_code'])->first();

        if (!$classroom) {
            return back()->with('error', 'Kode enrollment tidak valid.');
        }

        $user = Auth::user();

        if ($user->classrooms()->where('classroom_id', $classroom->id)->exists()) {
            return back()->with('error', 'Anda sudah terdaftar di kelas ini.');
        }

        $user->classrooms()->attach($classroom->id);

        return back()->with('success', 'Berhasil bergabung ke kelas: ' . $classroom->name);
    }

    /**
     * Transkrip Nilai — tampil dua mode (OBE & Konvensional) via query string ?mode=obe|konvensional
     */
    public function transkrip(Request $request)
    {
        $user      = Auth::user();
        $mode      = $request->input('mode', 'konvensional'); // default konvensional

        $classrooms = $user->classrooms()->with([
            'course.cpls',
            'course.cpmks.cpl',
            'course.cpmks.indicators.assessments.scores',
        ])->get()->sortBy(fn($c) => $c->academic_year . $c->period_type);

        // Ambil semua CPL yang ada
        $allCpls = Cpl::orderBy('code')->get();

        $transcriptRows = [];
        // Untuk akumulasi CPL: [cpl_id => [scores]]
        $cplAccumulator = [];

        foreach ($classrooms as $classroom) {
            $course = $classroom->course;
            if (!$course) continue;

            // Pakai template CPMK mata kuliah (tabel obe_cpmk) — sumber sama dengan dosen.
            $cpmks = $course->cpmks;

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

            $cpmkRows           = [];
            $cpmkScoresForFinal = [];

            foreach ($cpmks as $cpmk) {
                $cpmkScore = 0;

                foreach ($cpmk->indicators as $ind) {
                    $indWeight = (float) $ind->percentage / 100;
                    $indScore  = 0;

                    foreach ($ind->assessments as $asmnt) {
                        $compWeight = (float) $asmnt->percentage / 100;
                        $rawScore   = $scoreMap[$asmnt->id][$user->id] ?? null;
                        if ($rawScore !== null) {
                            $indScore += round($rawScore * $compWeight, 2);
                        }
                    }

                    $cpmkScore += round($indScore * $indWeight, 2);
                }

                $cpmkScoreRounded = round($cpmkScore, 2);
                $cpmkLulus        = GradeService::cpmkLulus($cpmkScoreRounded);

                $cpmkRows[] = [
                    'code'    => $cpmk->code,
                    'cpl_id'  => $cpmk->cpl_id,
                    'weight'  => $cpmk->percentage,
                    'score'   => $cpmkScoreRounded,
                    'lulus'   => $cpmkLulus,
                ];

                // Akumulasi ke CPL
                if ($cpmk->cpl_id) {
                    $cplAccumulator[$cpmk->cpl_id][] = $cpmkScoreRounded;
                }

                $cpmkScoresForFinal[] = ['score' => $cpmkScoreRounded, 'weight' => $cpmk->percentage];
            }

            $finalScore   = GradeService::nilaiAkhirMataKuliah($cpmkScoresForFinal);
            $konvensional = GradeService::toKonvensional($finalScore);
            $anyFailed    = collect($cpmkRows)->contains('lulus', false);

            $transcriptRows[] = [
                'classroom'   => $classroom,
                'course'      => $course,
                'cpmks'       => $cpmkRows,
                'final_score' => $finalScore,
                'final_grade' => $konvensional['huruf'],
                'final_mutu'  => $konvensional['mutu'],
                'final_lulus' => $konvensional['lulus'],
                'any_failed'  => $anyFailed,
            ];
        }

        // Hitung ketercapaian CPL — pembagian rata 100% ke semua CPMK pendukung.
        // CPL dengan N master-CPMK → tiap CPMK menyumbang 100/N %.
        // Persen ketercapaian = sum(score_taken) × (100/N) / 100 = sum(scores)/N.
        // CPMK yang belum diambil mahasiswa = 0 (tidak menyumbang).
        $cpmkSupportCount = Cpmk::selectRaw('cpl_id, COUNT(*) as total')
            ->whereNotNull('cpl_id')
            ->groupBy('cpl_id')
            ->pluck('total', 'cpl_id')
            ->toArray();

        $cplAchievement = [];
        foreach ($allCpls as $cpl) {
            $scores      = $cplAccumulator[$cpl->id] ?? [];
            $supportN    = (int) ($cpmkSupportCount[$cpl->id] ?? 0);
            $denominator = $supportN > 0 ? $supportN : count($scores);
            $achievement = ($denominator > 0 && count($scores))
                ? round(array_sum($scores) / $denominator, 1)
                : null;

            $cplAchievement[$cpl->id] = [
                'cpl'           => $cpl,
                'scores'        => $scores,
                'support_count' => $supportN,
                'taken_count'   => count($scores),
                'average'       => $achievement,
                'min_target'    => (float) ($cpl->min_target ?? 0),
            ];
        }

        // Hitung IPK dari mutu × SKS
        $ipkRows = collect($transcriptRows)->map(fn($r) => [
            'mutu' => $r['final_mutu'],
            'sks'  => $r['course']->sks ?? 0,
        ])->toArray();
        $ipk = GradeService::hitungIpk($ipkRows);

        $totalSks = collect($transcriptRows)->sum(fn($r) => $r['course']->sks ?? 0);

        return view('mahasiswa.transkrip', compact(
            'transcriptRows', 'user', 'mode', 'cplAchievement', 'allCpls', 'ipk', 'totalSks'
        ));
    }

    /**
     * KHS (Kartu Hasil Studi) — tampil & cetak per semester.
     * URL: /mahasiswa/khs?period_type=genap&academic_year=2025/2026
     */
    public function khs(Request $request)
    {
        $user = Auth::user();

        // ── Ambil semua kelas yang pernah diikuti, urutkan per semester ──
        $allClassrooms = $user->classrooms()
            ->with(['course.cpmks.indicators.assessments.scores'])
            ->get()
            ->sortBy(fn($c) => $c->academic_year . $c->period_type);

        // ── Buat daftar semester unik yang tersedia ──
        $semesterList = $allClassrooms
            ->map(fn($c) => [
                'period_type'   => $c->period_type,
                'academic_year' => $c->academic_year,
                'sort_key'      => $c->academic_year . ($c->period_type === 'genap' ? '2' : '1'),
            ])
            ->unique(fn($s) => $s['period_type'] . '|' . $s['academic_year'])
            ->sortBy('sort_key')
            ->values()
            ->toArray();

        // ── Tentukan semester yang aktif (dari query string atau semester terbaru) ──
        $activePeriodType   = $request->input('period_type');
        $activeAcademicYear = $request->input('academic_year');

        if (!$activePeriodType || !$activeAcademicYear) {
            // Default: semester terbaru
            $latest             = last($semesterList);
            $activePeriodType   = $latest['period_type']   ?? null;
            $activeAcademicYear = $latest['academic_year'] ?? null;
        }

        // ── Filter kelas sesuai semester aktif ──
        $semesterClassrooms = $allClassrooms->filter(
            fn($c) => $c->period_type === $activePeriodType
                   && $c->academic_year === $activeAcademicYear
        );

        // ── Hitung nilai per mata kuliah untuk semester ini ──
        $khsRows        = [];
        $totalSks       = 0;
        $totalNilaiSks  = 0.0;   // ∑ (mutu × SKS) untuk IPS semester ini

        foreach ($semesterClassrooms as $classroom) {
            $course = $classroom->course;
            if (!$course) continue;

            $cpmks    = $course->cpmks;
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

            $cpmkScoresForFinal = [];

            foreach ($cpmks as $cpmk) {
                $cpmkScore = 0;
                foreach ($cpmk->indicators as $ind) {
                    $indWeight = (float) $ind->percentage / 100;
                    $indScore  = 0;
                    foreach ($ind->assessments as $asmnt) {
                        $compWeight = (float) $asmnt->percentage / 100;
                        $rawScore   = $scoreMap[$asmnt->id][$user->id] ?? null;
                        if ($rawScore !== null) {
                            $indScore += round($rawScore * $compWeight, 2);
                        }
                    }
                    $cpmkScore += round($indScore * $indWeight, 2);
                }

                $cpmkScoresForFinal[] = [
                    'score'  => round($cpmkScore, 2),
                    'weight' => $cpmk->percentage,
                ];
            }

            $finalScore   = \App\Services\GradeService::nilaiAkhirMataKuliah($cpmkScoresForFinal);
            $konvensional = \App\Services\GradeService::toKonvensional($finalScore);
            $anyFailed    = collect($cpmkScoresForFinal)->contains(
                fn($c) => !\App\Services\GradeService::cpmkLulus($c['score'])
            );

            $sks      = (int) ($course->sks ?? 0);
            $mutu     = $konvensional['mutu'];
            $nilaiSks = round($mutu * $sks, 2);  // Bobot × SKS

            // Hitung KE (berapa kali mata kuliah ini diambil mahasiswa)
            $ke = $user->classrooms()
                ->where('course_id', $course->id)
                ->whereRaw("CONCAT(academic_year, period_type) <= ?", [$activeAcademicYear . $activePeriodType])
                ->count();

            $khsRows[] = [
                'classroom'   => $classroom,
                'course'      => $course,
                'final_score' => $finalScore,
                'final_grade' => $konvensional['huruf'],
                'final_mutu'  => $mutu,
                'final_lulus' => $konvensional['lulus'],
                'any_failed'  => $anyFailed,
                'ke'          => max(1, $ke),
                'nilai_sks'   => $nilaiSks,
            ];

            $totalSks      += $sks;
            $totalNilaiSks += $nilaiSks;
        }

        // ── IPS semester ini ──
        $ips = $totalSks > 0 ? round($totalNilaiSks / $totalSks, 2) : 0.0;

        // ── IPK kumulatif (semua semester s.d. semester aktif) ──
        $ipkRows = $allClassrooms
            ->filter(fn($c) =>
                $c->academic_year < $activeAcademicYear
                || ($c->academic_year === $activeAcademicYear
                    && ($activePeriodType === 'genap' || $c->period_type === $activePeriodType))
            )
            ->map(function ($c) use ($user) {
                // Hitung mutu untuk kelas ini (ulang kalkulasi cepat)
                $course = $c->course;
                if (!$course) return null;

                $cpmks    = $course->cpmks;
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
                $scores = [];
                foreach ($cpmks as $cpmk) {
                    $cpmkScore = 0;
                    foreach ($cpmk->indicators as $ind) {
                        $iw = (float) $ind->percentage / 100;
                        $is = 0;
                        foreach ($ind->assessments as $asmnt) {
                            $cw  = (float) $asmnt->percentage / 100;
                            $raw = $scoreMap[$asmnt->id][$user->id] ?? null;
                            if ($raw !== null) $is += round($raw * $cw, 2);
                        }
                        $cpmkScore += round($is * $iw, 2);
                    }
                    $scores[] = ['score' => round($cpmkScore, 2), 'weight' => $cpmk->percentage];
                }
                $finalScore = \App\Services\GradeService::nilaiAkhirMataKuliah($scores);
                $mutu       = \App\Services\GradeService::toKonvensional($finalScore)['mutu'];
                return ['mutu' => $mutu, 'sks' => (int) ($course->sks ?? 0)];
            })
            ->filter()
            ->values()
            ->toArray();

        $ipk = \App\Services\GradeService::hitungIpk($ipkRows);

        // ── Maks. Beban SKS semester berikutnya (aturan UNRI) ──
        $maxSksBerikutnya = 24; // default
        if ($ips >= 3.00)      $maxSksBerikutnya = 24;
        elseif ($ips >= 2.50)  $maxSksBerikutnya = 22;
        elseif ($ips >= 2.00)  $maxSksBerikutnya = 20;
        else                   $maxSksBerikutnya = 18;

        // ── Data profil mahasiswa ──
        $profile   = $user->profilMahasiswa()->with('programStudi.jurusan')->first();
        $namaProdi = $profile?->programStudi?->nama_prodi ?? 'Teknik Informatika';
        $angkatan  = $profile?->nim
            ? substr($profile->nim, 0, 4)
            : (strlen($user->identity ?? '') >= 4 ? substr($user->identity, 0, 4) : '-');

        // ── Pembimbing akademik: ambil dari relasi jika ada, fallback kosong ──
        $pembimbingAkademik = null; // Tambahkan relasi jika tersedia

        // ── Wakil dekan: bisa dikonfigurasi via Setting atau hardcode ──
        $wakilDekan    = 'Prof. Dr. Ir. Azriyenni, ST., M.Eng';
        $nipWakilDekan = '197304011999032003';

        return view('mahasiswa.khs', compact(
            'user',
            'khsRows',
            'semesterList',
            'activePeriodType',
            'activeAcademicYear',
            'totalSks',
            'totalNilaiSks',
            'ips',
            'ipk',
            'maxSksBerikutnya',
            'namaProdi',
            'angkatan',
            'pembimbingAkademik',
            'wakilDekan',
            'nipWakilDekan'
        ));
    }
    
    public function downloadKonvensional()
    {
        $user = Auth::user();
    
        $classrooms = $user->classrooms()->with([
            'course.cpls',
            'course.cpmks.cpl',
            'course.cpmks.indicators.assessments.scores',
        ])->get()->sortBy(fn($c) => $c->academic_year . $c->period_type);
    
        $transcriptRows = [];
    
        foreach ($classrooms as $classroom) {
            $course = $classroom->course;
            if (!$course) continue;
    
            $cpmks    = $course->cpmks;
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
    
            $cpmkRows           = [];
            $cpmkScoresForFinal = [];
    
            foreach ($cpmks as $cpmk) {
                $cpmkScore = 0;
                foreach ($cpmk->indicators as $ind) {
                    $indWeight = (float) $ind->percentage / 100;
                    $indScore  = 0;
                    foreach ($ind->assessments as $asmnt) {
                        $compWeight = (float) $asmnt->percentage / 100;
                        $rawScore   = $scoreMap[$asmnt->id][$user->id] ?? null;
                        if ($rawScore !== null) {
                            $indScore += round($rawScore * $compWeight, 2);
                        }
                    }
                    $cpmkScore += round($indScore * $indWeight, 2);
                }
    
                $cpmkScoreRounded = round($cpmkScore, 2);
                $cpmkRows[] = [
                    'code'   => $cpmk->code,
                    'cpl_id' => $cpmk->cpl_id,
                    'weight' => $cpmk->percentage,
                    'score'  => $cpmkScoreRounded,
                    'lulus'  => GradeService::cpmkLulus($cpmkScoreRounded),
                ];
                $cpmkScoresForFinal[] = ['score' => $cpmkScoreRounded, 'weight' => $cpmk->percentage];
            }
    
            $finalScore   = GradeService::nilaiAkhirMataKuliah($cpmkScoresForFinal);
            $konvensional = GradeService::toKonvensional($finalScore);
            $anyFailed    = collect($cpmkRows)->contains('lulus', false);
    
            $transcriptRows[] = [
                'classroom'   => $classroom,
                'course'      => $course,
                'cpmks'       => $cpmkRows,
                'final_score' => $finalScore,
                'final_grade' => $konvensional['huruf'],
                'final_mutu'  => $konvensional['mutu'],
                'final_lulus' => $konvensional['lulus'],
                'any_failed'  => $anyFailed,
            ];
        }
    
        $ipkRows  = collect($transcriptRows)->map(fn($r) => ['mutu' => $r['final_mutu'], 'sks' => $r['course']->sks ?? 0])->toArray();
        $ipk      = GradeService::hitungIpk($ipkRows);
        $totalSks = collect($transcriptRows)->sum(fn($r) => $r['course']->sks ?? 0);
    
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mahasiswa.transkrip.pdf-konvensional', compact(
            'user', 'transcriptRows', 'totalSks', 'ipk'
        ))->setPaper('a4', 'portrait');
    
        return $pdf->download('transkrip-konvensional-' . $user->identity . '.pdf');
    }
    
    public function downloadObe()
    {
        $user = Auth::user();
    
        $classrooms = $user->classrooms()->with([
            'course.cpls',
            'course.cpmks.cpl',
            'course.cpmks.indicators.assessments.scores',
        ])->get()->sortBy(fn($c) => $c->academic_year . $c->period_type);
    
        $allCpls        = Cpl::orderBy('code')->get();
        $transcriptRows = [];
        $cplAccumulator = [];
    
        foreach ($classrooms as $classroom) {
            $course = $classroom->course;
            if (!$course) continue;
    
            $cpmks    = $course->cpmks;
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
    
            $cpmkRows           = [];
            $cpmkScoresForFinal = [];
    
            foreach ($cpmks as $cpmk) {
                $cpmkScore = 0;
                foreach ($cpmk->indicators as $ind) {
                    $indWeight = (float) $ind->percentage / 100;
                    $indScore  = 0;
                    foreach ($ind->assessments as $asmnt) {
                        $compWeight = (float) $asmnt->percentage / 100;
                        $rawScore   = $scoreMap[$asmnt->id][$user->id] ?? null;
                        if ($rawScore !== null) {
                            $indScore += round($rawScore * $compWeight, 2);
                        }
                    }
                    $cpmkScore += round($indScore * $indWeight, 2);
                }
    
                $cpmkScoreRounded = round($cpmkScore, 2);
                $cpmkRows[] = [
                    'code'   => $cpmk->code,
                    'cpl_id' => $cpmk->cpl_id,
                    'weight' => $cpmk->percentage,
                    'score'  => $cpmkScoreRounded,
                    'lulus'  => GradeService::cpmkLulus($cpmkScoreRounded),
                ];
    
                if ($cpmk->cpl_id) {
                    $cplAccumulator[$cpmk->cpl_id][] = $cpmkScoreRounded;
                }
    
                $cpmkScoresForFinal[] = ['score' => $cpmkScoreRounded, 'weight' => $cpmk->percentage];
            }
    
            $finalScore   = GradeService::nilaiAkhirMataKuliah($cpmkScoresForFinal);
            $konvensional = GradeService::toKonvensional($finalScore);
            $anyFailed    = collect($cpmkRows)->contains('lulus', false);
    
            $transcriptRows[] = [
                'classroom'   => $classroom,
                'course'      => $course,
                'cpmks'       => $cpmkRows,
                'final_score' => $finalScore,
                'final_grade' => $konvensional['huruf'],
                'final_mutu'  => $konvensional['mutu'],
                'final_lulus' => $konvensional['lulus'],
                'any_failed'  => $anyFailed,
            ];
        }
    
        $cpmkSupportCount = Cpmk::selectRaw('cpl_id, COUNT(*) as total')
            ->whereNotNull('cpl_id')
            ->groupBy('cpl_id')
            ->pluck('total', 'cpl_id')
            ->toArray();
    
        $cplAchievement = [];
        foreach ($allCpls as $cpl) {
            $scores      = $cplAccumulator[$cpl->id] ?? [];
            $supportN    = (int) ($cpmkSupportCount[$cpl->id] ?? 0);
            $denominator = $supportN > 0 ? $supportN : count($scores);
            $achievement = ($denominator > 0 && count($scores))
                ? round(array_sum($scores) / $denominator, 1)
                : null;
    
            $cplAchievement[$cpl->id] = [
                'cpl'           => $cpl,
                'scores'        => $scores,
                'support_count' => $supportN,
                'taken_count'   => count($scores),
                'average'       => $achievement,
                'min_target'    => (float) ($cpl->min_target ?? 0),
            ];
        }
    
        $ipkRows  = collect($transcriptRows)->map(fn($r) => ['mutu' => $r['final_mutu'], 'sks' => $r['course']->sks ?? 0])->toArray();
        $ipk      = GradeService::hitungIpk($ipkRows);
        $totalSks = collect($transcriptRows)->sum(fn($r) => $r['course']->sks ?? 0);
    
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mahasiswa.transkrip.pdf-obe', compact(
            'user', 'transcriptRows', 'cplAchievement', 'totalSks', 'ipk'
        ))->setPaper('a4', 'portrait');
    
        return $pdf->download('transkrip-obe-' . $user->identity . '.pdf');
    }
}