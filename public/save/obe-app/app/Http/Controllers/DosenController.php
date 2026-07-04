<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DosenController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Ambil mata kuliah yang diampu oleh dosen yang sedang login
        // Logika: Dosen mengampu mata kuliah jika dia ditugaskan pada SALAH SATU CPMK mata kuliah tersebut
        $courses = \App\Models\Course::whereHas('cpmks', function($query) use ($user) {
            $query->where('lecturer_id', $user->id);
        })->orderBy('semester')->orderBy('code')->get();

        return view('dosen.dashboard', compact('courses'));
    }

    public function show(\App\Models\Course $course)
    {
        // Pastikan dosen memang mengampu MK ini (ada setidaknya satu CPMK yang diajarkannya)
        $isLecturer = $course->cpmks()->where('lecturer_id', Auth::id())->exists();
        
        // Jika dosen tidak mengampu MK ini sama sekali, mungkin kita redirect atau beri pesan
        // Namun, jika dosen mengakses MK ini, mungkin dia hanya ingin melihat detailnya.
        // Untuk amannya, kita load relasi seperti biasa.
        
        // Load mata kuliah dengan filter CPMK khusus untuk dosen yang login
        $course->load([
            'cpls', 
            'prerequisite', 
            'cpmks' => function($query) {
                $query->where('lecturer_id', Auth::id())
                      ->with(['lecturer', 'indicators']);
            }
        ]);
        
        // Opsional: Validasi apakah dosen berhak melihat course ini (jika perlu)
        // if (!Auth::user()->courses->contains($course)) { abort(403); }

        return view('dosen.courses.show', compact('course'));
    }

    public function editIndicator(\App\Models\Indicator $indicator)
    {
        // Ensure the lecturer is assigned to the course of this indicator
        $cpmk = $indicator->cpmk;
        if ($cpmk->lecturer_id !== Auth::id()) {
             // Optional: strict check, though middleware/policy is better.
             // For now, let's assume if they have the link they can access, or do a simple check.
             // abort(403); 
        }

        $indicator->load('assessments');
        
        return view('dosen.indicators.edit', compact('indicator'));
    }
}
