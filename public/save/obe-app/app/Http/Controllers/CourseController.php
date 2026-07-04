<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $semester = $request->input('semester');
        
        $query = Course::with(['users', 'cpmks.lecturer']); // Eager load lecturers from course and CPMKs

        if ($semester) {
            $query->where('semester', $semester);
        }

        $courses = $query->orderBy('semester')
                         ->orderBy('code')
                         ->paginate(10)
                         ->withQueryString();

        return view('kaprodi.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('code')->get(); // For prerequisites
        $cpls = \App\Models\Cpl::orderBy('code')->get();
        return view('kaprodi.courses.create', compact('courses', 'cpls'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses',
            'name' => 'required|string',
            'sks' => 'required|integer|min:1',
            'semester' => 'required|integer|min:1|max:8',
            'prerequisite_course_id' => 'nullable|exists:courses,id',
            'cpl_ids' => 'required|array|min:1',
            'cpl_ids.*' => 'exists:cpls,id',
        ]);

        $course = Course::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'sks' => $validated['sks'],
            'semester' => $validated['semester'],
            'prerequisite_course_id' => $validated['prerequisite_course_id'],
        ]);

        // Attach CPLs
        $course->cpls()->attach($validated['cpl_ids']);

        // Redirect to detail page (CPMK management)
        return redirect()->route('courses.show', $course)->with('success', 'Mata Kuliah berhasil ditambahkan. Silakan tambahkan CPMK.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $course->load(['cpls', 'prerequisite', 'cpmks.lecturer', 'cpmks.indicators']);
        return view('kaprodi.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $courses = Course::where('id', '!=', $course->id)->orderBy('code')->get(); // Exclude current course from prerequisites
        $cpls = \App\Models\Cpl::orderBy('code')->get();
        $course->load(['cpls', 'cpmks.lecturer']); // Load existing CPL relations and CPMK with lecturer
        
        return view('kaprodi.courses.edit', compact('course', 'courses', 'cpls'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code,' . $course->id,
            'name' => 'required|string',
            'sks' => 'required|integer|min:1',
            'semester' => 'required|integer|min:1|max:8',
            'prerequisite_course_id' => 'nullable|exists:courses,id',
            'cpl_ids' => 'required|array|min:1',
            'cpl_ids.*' => 'exists:cpls,id',
        ]);

        $course->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'sks' => $validated['sks'],
            'semester' => $validated['semester'],
            'prerequisite_course_id' => $validated['prerequisite_course_id'],
        ]);

        // Sync CPLs (remove old, add new)
        $course->cpls()->sync($validated['cpl_ids']);

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
