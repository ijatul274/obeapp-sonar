<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\ProgramStudi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramStudiController extends Controller
{
    public function byJurusan(Jurusan $jurusan): JsonResponse
    {
        $prodi = ProgramStudi::where('jurusan_id', $jurusan->id)
            ->orderBy('nama_prodi')
            ->get(['id', 'nama_prodi', 'kode']);

        return response()->json($prodi);
    }

    public function index()
    {
        $auth = Auth::user();

        $prodiQuery = ProgramStudi::with('jurusan')->orderBy('nama_prodi');
        $jurusanQuery = Jurusan::orderBy('nama_jurusan');

        // Admin jurusan hanya melihat prodi dari jurusannya
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $prodiQuery->where('jurusan_id', $auth->jurusan_id);
            $jurusanQuery->where('id', $auth->jurusan_id);
        }

        $prodi   = $prodiQuery->get();
        $jurusan = $jurusanQuery->get();

        return view('admin.akademik.prodi.index', compact('prodi', 'jurusan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode'       => 'nullable|string|max:20|unique:obe_program_studi,kode',
            'nama_prodi' => 'required|string|max:150',
            'jurusan_id' => 'nullable|exists:obe_jurusan,id',
        ]);
        if (!empty($data['kode'])) {
            $data['kode'] = strtoupper($data['kode']);
        }

        ProgramStudi::create($data);
        return redirect()->route('admin.prodi.index')
            ->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function update(Request $request, ProgramStudi $prodi)
    {
        $data = $request->validate([
            'kode'       => 'nullable|string|max:20|unique:obe_program_studi,kode,' . $prodi->id,
            'nama_prodi' => 'required|string|max:150',
            'jurusan_id' => 'nullable|exists:obe_jurusan,id',
        ]);
        if (!empty($data['kode'])) {
            $data['kode'] = strtoupper($data['kode']);
        }

        $prodi->update($data);
        return redirect()->route('admin.prodi.index')
            ->with('success', 'Program studi berhasil diperbarui.');
    }

    public function destroy(ProgramStudi $prodi)
    {
        $prodi->delete();
        return redirect()->route('admin.prodi.index')
            ->with('success', 'Program studi berhasil dihapus.');
    }
}