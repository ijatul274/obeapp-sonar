<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        $semester = Semester::currentOrCreate();
        return view('admin.akademik.semester.index', compact('semester'));
    }

    public function update(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'periode'         => 'required|in:ganjil,genap',
            'tahun_ajaran'    => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $semester->update($data);

        return redirect()->route('admin.semester.index')
            ->with('success', 'Data semester berhasil diperbarui.');
    }
}
