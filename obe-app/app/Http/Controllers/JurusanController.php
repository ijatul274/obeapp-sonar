<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurusanController extends Controller
{
    public function index()
    {
        $auth = Auth::user();

        $jurusanQuery = Jurusan::with('prodi')->orderBy('nama_jurusan');
        $prodiQuery   = ProgramStudi::orderBy('nama_prodi');

        // Admin jurusan hanya melihat jurusannya sendiri
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $jurusanQuery->where('id', $auth->jurusan_id);
            $prodiQuery->where('jurusan_id', $auth->jurusan_id);
        }

        $jurusan    = $jurusanQuery->get();
        $semuaProdi = $prodiQuery->get();

        return view('admin.akademik.jurusan.index', compact('jurusan', 'semuaProdi'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode'         => 'nullable|string|max:20|unique:obe_jurusan,kode',
            'nama_jurusan' => 'required|string|max:150',
        ]);
        if (!empty($data['kode'])) {
            $data['kode'] = strtoupper($data['kode']);
        }

        Jurusan::create($data);
        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $data = $request->validate([
            'kode'         => 'nullable|string|max:20|unique:obe_jurusan,kode,' . $jurusan->id,
            'nama_jurusan' => 'required|string|max:150',
        ]);
        if (!empty($data['kode'])) {
            $data['kode'] = strtoupper($data['kode']);
        }

        $jurusan->update($data);
        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    /**
     * Assign/unassign prodi ke jurusan ini.
     * Prodi yang dipilih → jurusan_id = $jurusan->id
     * Prodi yang dihapus dari jurusan ini → jurusan_id = null
     */
    public function assignProdi(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'prodi_ids'   => 'nullable|array',
            'prodi_ids.*' => 'integer|exists:obe_program_studi,id',
        ]);

        $selectedIds = $request->input('prodi_ids', []);

        // Lepaskan prodi yang sebelumnya di jurusan ini tapi sekarang tidak dipilih
        ProgramStudi::where('jurusan_id', $jurusan->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['jurusan_id' => null]);

        // Assign prodi yang dipilih ke jurusan ini
        if (!empty($selectedIds)) {
            ProgramStudi::whereIn('id', $selectedIds)
                ->update(['jurusan_id' => $jurusan->id]);
        }

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Program studi jurusan ' . $jurusan->nama_jurusan . ' berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        if ($jurusan->users()->exists()) {
            return redirect()->route('admin.jurusan.index')
                ->with('error', "Jurusan {$jurusan->nama_jurusan} masih memiliki pengguna terkait, tidak dapat dihapus.");
        }

        $jurusan->delete();
        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}