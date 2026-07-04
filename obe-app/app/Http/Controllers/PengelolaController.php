<?php

namespace App\Http\Controllers;

use App\Models\Pengelola;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengelolaController extends Controller
{
    private array $jabatanOptions = ['kaprodi', 'kajur', 'pj_lab', 'koordinator', 'sekretaris'];

    public function index()
    {
        $auth = Auth::user();

        $pengelolaQuery = Pengelola::with('user')
            ->orderBy('aktif', 'desc')
            ->orderBy('jabatan');

        // Admin jurusan hanya melihat pengelola dari jurusannya
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $pengelolaQuery->whereHas('user', fn($q) => $q->where('jurusan_id', $auth->jurusan_id));
        }

        $pengelola = $pengelolaQuery->get();

        $dosenQuery = User::dosenAkademik()->orderBy('name');
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $dosenQuery->where('jurusan_id', $auth->jurusan_id);
        }
        $dosen = $dosenQuery->get(['id', 'name', 'identity', 'role']);

        return view('admin.pengelola.index', [
            'pengelola'      => $pengelola,
            'dosen'          => $dosen,
            'jabatanOptions' => $this->jabatanOptions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'          => 'required|exists:obe_pengguna,id',
            'jabatan'          => 'required|string|max:60',
            'keterangan'       => 'nullable|string|max:255',
            'mulai_menjabat'   => 'nullable|date',
            'selesai_menjabat' => 'nullable|date|after_or_equal:mulai_menjabat',
            'aktif'            => 'sometimes|boolean',
        ]);
        $data['aktif'] = $request->boolean('aktif', true);

        Pengelola::create($data);

        return redirect()->route('admin.pengelola.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Pengelola $pengelola)
    {
        $data = $request->validate([
            'jabatan'          => 'required|string|max:60',
            'keterangan'       => 'nullable|string|max:255',
            'mulai_menjabat'   => 'nullable|date',
            'selesai_menjabat' => 'nullable|date|after_or_equal:mulai_menjabat',
            'aktif'            => 'sometimes|boolean',
        ]);
        $data['aktif'] = $request->boolean('aktif', false);

        $pengelola->update($data);

        return redirect()->route('admin.pengelola.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Pengelola $pengelola)
    {
        $pengelola->delete();
        return redirect()->route('admin.pengelola.index')
            ->with('success', 'Jabatan berhasil dihapus.');
    }
}