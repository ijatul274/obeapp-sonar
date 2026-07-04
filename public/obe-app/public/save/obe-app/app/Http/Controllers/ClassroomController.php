<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Classroom::with('course')->where('is_archived', false);

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $classrooms = $query->orderBy('semester')->orderBy('name')->get();

        return view('kaprodi.classrooms.index', compact('classrooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = \App\Models\Course::orderBy('name')->get();
        return view('kaprodi.classrooms.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:8',
            'course_id' => 'required|exists:courses,id',
        ]);

        // Generate unique enrollment code
        do {
            $enrollmentCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (Classroom::where('enrollment_code', $enrollmentCode)->exists());

        $validated['enrollment_code'] = $enrollmentCode;

        Classroom::create($validated);

        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classroom $classroom)
    {
        $classroom->load(['course.users', 'course.cpmks', 'students']);
        return view('kaprodi.classrooms.edit', compact('classroom'));
    }

    /**
     * Archive or unarchive a classroom.
     */
    public function archive(Classroom $classroom)
    {
        $classroom->update(['is_archived' => !$classroom->is_archived]);
        
        $message = $classroom->is_archived 
            ? 'Kelas berhasil diarsipkan.' 
            : 'Kelas berhasil dikembalikan dari arsip.';
        
        return redirect()->route('classrooms.index')->with('success', $message);
    }

    /**
     * Unenroll a student from a classroom.
     */
    public function unenroll(Classroom $classroom, $studentId)
    {
        $classroom->students()->detach($studentId);
        
        return redirect()->back()->with('success', 'Mahasiswa berhasil dihapus dari kelas.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return redirect()->route('classrooms.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
