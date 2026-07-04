<?php

namespace App\Http\Controllers;

use App\Models\Konsentrasi;
use Illuminate\Http\Request;

class KonsentrasiController extends Controller
{
    public function index()
    {
        $konsentrasi = Konsentrasi::orderBy('kode')->get();
        return view('admin.akademik.konsentrasi.index', compact('konsentrasi'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:10|unique:obe_konsentrasi,kode',
            'nama' => 'required|string|max:150',
        ]);
        $data['kode'] = strtoupper($data['kode']);

        Konsentrasi::create($data);
        return redirect()->route('admin.konsentrasi.index')
            ->with('success', 'Konsentrasi berhasil ditambahkan.');
    }

    public function update(Request $request, Konsentrasi $konsentrasi)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:10|unique:obe_konsentrasi,kode,' . $konsentrasi->id,
            'nama' => 'required|string|max:150',
        ]);
        $data['kode'] = strtoupper($data['kode']);

        $konsentrasi->update($data);
        return redirect()->route('admin.konsentrasi.index')
            ->with('success', 'Konsentrasi berhasil diperbarui.');
    }

    public function destroy(Konsentrasi $konsentrasi)
    {
        $kode = $konsentrasi->kode;
        $inUse = \App\Models\MahasiswaProfile::where('konsentrasi', $kode)->exists();
        if ($inUse) {
            return redirect()->route('admin.konsentrasi.index')
                ->with('error', "Konsentrasi {$kode} masih digunakan oleh mahasiswa, tidak dapat dihapus.");
        }

        $konsentrasi->delete();
        return redirect()->route('admin.konsentrasi.index')
            ->with('success', 'Konsentrasi berhasil dihapus.');
    }
}
