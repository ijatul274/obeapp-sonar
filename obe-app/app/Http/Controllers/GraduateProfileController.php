<?php

namespace App\Http\Controllers;

use App\Models\GraduateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GraduateProfileController extends Controller
{
    /**
     * Ambil program_studi_id aktif user yang sedang login.
     */
    private function activeProdiId(): ?int
    {
        return Auth::user()?->activeProdiId();
    }

    /**
     * Base query GraduateProfile — scope KETAT ke prodi aktif, tanpa fallback NULL.
     */
    private function profileQuery()
    {
        $query   = GraduateProfile::orderBy('name');
        $prodiId = $this->activeProdiId();

        if ($prodiId) {
            $query->where('program_studi_id', $prodiId);
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profiles = $this->profileQuery()->get();
        return view('kaprodi.graduate-profiles.index', compact('profiles'));
    }

    public function dashboard()
    {
        $auth    = auth()->user();
        $prodiId = $auth?->activeProdiId();

        // Course: scope KETAT ke program_studi_id, tanpa fallback NULL
        $courseQuery = \App\Models\Course::with(['cpmks.cpl'])
            ->orderBy('semester')
            ->orderBy('code');

        if ($auth && in_array($auth->role, ['kaprodi', 'admin_jurusan'])) {
            if ($prodiId) {
                $courseQuery->where('program_studi_id', $prodiId);
            } elseif ($auth->jurusan_id) {
                // Kaprodi belum dikonfigurasi prodinya → fallback ke jurusan
                $courseQuery->where('jurusan_id', $auth->jurusan_id);
            }
        }

        $courses = $courseQuery->get();

        // CPL: scope KETAT ke prodi, tanpa fallback NULL
        $cplQuery = \App\Models\Cpl::orderBy('code');
        if ($prodiId) {
            $cplQuery->where('program_studi_id', $prodiId);
        }
        $cpls = $cplQuery->get();

        // Build lookup: course_id => Set of cpl_id yang didukung CPMK-nya
        $courseCplMap = [];
        foreach ($courses as $course) {
            $courseCplMap[$course->id] = $course->cpmks
                ->pluck('cpl_id')
                ->filter()
                ->unique()
                ->flip()
                ->toArray();
        }

        return view('kaprodi.dashboard', compact('courses', 'cpls', 'courseCplMap'));
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
        $prodiId = $this->activeProdiId();

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Otomatis ikat ke prodi kaprodi yang sedang login
        $validated['program_studi_id'] = $prodiId;

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
            'name'        => 'required|string|max:255',
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