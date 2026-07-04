<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use Illuminate\Http\Request;

class AssessmentScoreController extends Controller
{
    public function index(Assessment $assessment)
    {
        // Load course to get students
        $assessment->load('indicator.cpmk.course');
        $course = $assessment->indicator->cpmk->course;
        
        // Get students enrolled in the course
        $students = $course->users()->where('role', 'mahasiswa')->orderBy('identity')->get();
        
        // Get existing scores key-value pair [student_id => score]
        $scores = $assessment->scores()->pluck('score', 'student_id');

        return view('dosen.assessments.show', compact('assessment', 'students', 'scores'));
    }

    public function store(Assessment $assessment, Request $request)
    {
        $data = $request->validate([
            'scores' => 'array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($data['scores'] ?? [] as $studentId => $score) {
            if ($score !== null && $score !== '') {
                AssessmentScore::updateOrCreate(
                    ['assessment_id' => $assessment->id, 'student_id' => $studentId],
                    ['score' => $score]
                );
            } else {
                // If input corresponds to an existing score but is now empty, delete it
                AssessmentScore::where('assessment_id', $assessment->id)
                    ->where('student_id', $studentId)
                    ->delete();
            }
        }

        return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
    }
}
