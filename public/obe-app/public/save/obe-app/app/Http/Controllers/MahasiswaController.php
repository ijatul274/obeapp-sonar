<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $classrooms = $user->classrooms()->with('course')->get();
        
        return view('mahasiswa.dashboard', compact('classrooms'));
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

        // Check if already enrolled
        if ($user->classrooms()->where('classroom_id', $classroom->id)->exists()) {
            return back()->with('error', 'Anda sudah terdaftar di kelas ini.');
        }

        // Enroll student
        $user->classrooms()->attach($classroom->id);

        return back()->with('success', 'Berhasil bergabung ke kelas: ' . $classroom->name);
    }
}
