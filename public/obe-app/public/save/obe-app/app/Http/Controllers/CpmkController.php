<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Cpmk;
use App\Models\User;
use Illuminate\Http\Request;

class CpmkController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        // Get lecturers (users with dosen role)
        $lecturers = User::where('role', 'dosen')->get();
        
        return view('kaprodi.cpmks.create', compact('course', 'lecturers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'code' => 'required|string',
            'description' => 'required|string',
            'lecturer_id' => 'nullable|exists:users,id',
            'indicators' => 'nullable|array',
            'indicators.*' => 'nullable|string',
        ]);

        // Create CPMK
        $cpmk = Cpmk::create([
            'course_id' => $validated['course_id'],
            'code' => $validated['code'],
            'description' => $validated['description'],
            'lecturer_id' => $validated['lecturer_id'] ?? null,
        ]);

        // Create Indicators if provided
        if (!empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $indicatorDescription) {
                if (!empty($indicatorDescription)) {
                    $cpmk->indicators()->create([
                        'description' => $indicatorDescription,
                    ]);
                }
            }
        }

        return redirect()->route('courses.edit', $validated['course_id'])->with('success', 'CPMK berhasil ditambahkan.');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cpmk $cpmk)
    {
        // Get lecturers (users with dosen role)
        $lecturers = User::where('role', 'dosen')->get();
        $course = $cpmk->course;
        $cpmk->load('indicators');
        
        return view('kaprodi.cpmks.edit', compact('cpmk', 'course', 'lecturers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cpmk $cpmk)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'description' => 'required|string',
            'lecturer_id' => 'nullable|exists:users,id',
            'indicators' => 'nullable|array',
            'indicators.*' => 'nullable|string',
        ]);

        // Update CPMK
        $cpmk->update([
            'code' => $validated['code'],
            'description' => $validated['description'],
            'lecturer_id' => $validated['lecturer_id'] ?? null,
        ]);

        // Update Indicators (Simple strategy: Delete all and recreate)
        // This is safe here because indicators are simple text strings belonging only to this CPMK.
        // If there were relationships to other tables from indicators, we would need a more complex sync.
        $cpmk->indicators()->delete();
        
        if (!empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $indicatorDescription) {
                if (!empty($indicatorDescription)) {
                    $cpmk->indicators()->create([
                        'description' => $indicatorDescription,
                    ]);
                }
            }
        }

        return redirect()->route('courses.edit', $cpmk->course_id)->with('success', 'CPMK berhasil diperbarui.');
    }
    /**
     * Remove the specified resource from storage.
     */
    /**
     * Display the specified resource.
     */
    public function show(Cpmk $cpmk)
    {
        $cpmk->load(['course', 'lecturer', 'indicators']);
        return view('kaprodi.cpmks.show', compact('cpmk'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cpmk $cpmk)
    {
        $courseId = $cpmk->course_id;
        $cpmk->delete();
        return redirect()->route('courses.edit', $courseId)->with('success', 'CPMK berhasil dihapus.');
    }
}
