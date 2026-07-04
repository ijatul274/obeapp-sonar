<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentScoreController extends Controller
{
    public function index(Assessment $assessment)
    {
        $assessment->load('indicator.cpmk.cpl', 'indicator.cpmk.course.classrooms', 'scores');
        $course = $assessment->indicator->cpmk->course;

        // Ambil mahasiswa dari semua classroom yang terhubung ke course ini
        $students = \Illuminate\Support\Facades\DB::table('obe_pengguna')
            ->join('obe_kelas_pengguna', 'obe_pengguna.id', '=', 'obe_kelas_pengguna.user_id')
            ->join('obe_kelas', 'obe_kelas.id', '=', 'obe_kelas_pengguna.classroom_id')
            ->where('obe_kelas.course_id', $course->id)
            ->where('obe_pengguna.role', 'mahasiswa')
            ->select('obe_pengguna.id', 'obe_pengguna.name', 'obe_pengguna.identity')
            ->orderBy('obe_pengguna.identity')
            ->distinct()
            ->get();

        // Nilai yang sudah ada [student_id => score]
        $scores = $assessment->scores()->pluck('score', 'student_id');

        return view('dosen.assessments.show', compact('assessment', 'students', 'scores'));
    }

    public function store(Assessment $assessment, Request $request)
    {
        $data = $request->validate([
            'scores'   => 'array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($data['scores'] ?? [] as $studentId => $score) {
            if ($score !== null && $score !== '') {
                AssessmentScore::updateOrCreate(
                    ['assessment_id' => $assessment->id, 'student_id' => $studentId],
                    ['score' => $score]
                );
            } else {
                AssessmentScore::where('assessment_id', $assessment->id)
                    ->where('student_id', $studentId)
                    ->delete();
            }
        }

        return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
    }
}
