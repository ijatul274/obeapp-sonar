<?php

namespace App\Http\Controllers;

use App\Models\GraduateProfile;
use Illuminate\Http\Request;

class GraduateProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profiles = GraduateProfile::all();
        return view('kaprodi.graduate-profiles.index', compact('profiles'));
    }

    public function dashboard()
    {
        $courses = \App\Models\Course::with('cpls')->get();
        $cpls = \App\Models\Cpl::all();
        
        return view('kaprodi.dashboard', compact('courses', 'cpls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kaprodi.graduate-profiles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        GraduateProfile::create($validated);

        return redirect()->route('graduate-profiles.index')
            ->with('success', 'Profil Lulusan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GraduateProfile $graduateProfile)
    {
        return view('kaprodi.graduate-profiles.edit', compact('graduateProfile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GraduateProfile $graduateProfile)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $graduateProfile->update($validated);

        return redirect()->route('graduate-profiles.index')
            ->with('success', 'Profil Lulusan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GraduateProfile $graduateProfile)
    {
        $graduateProfile->delete();

        return redirect()->route('graduate-profiles.index')
            ->with('success', 'Profil Lulusan berhasil dihapus!');
    }
}
