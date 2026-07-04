<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CplController extends Controller
{
    /**
     * Ambil program_studi_id aktif user yang sedang login.
     */
    private function activeProdiId(): ?int
    {
        return Auth::user()?->activeProdiId();
    }

    /**
     * Base query CPL — scope KETAT ke prodi aktif, tanpa fallback NULL.
     */
    private function cplQuery()
    {
        $query   = Cpl::orderBy('code');
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
        $cpls = $this->cplQuery()->get();
        return view('kaprodi.cpls.index', compact('cpls'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kaprodi.cpls.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $prodiId = $this->activeProdiId();

        $validated = $request->validate([
            'code'        => 'required|string|max:255|unique:obe_cpl,code',
            'description' => 'required|string',
            'min_target'  => 'required|numeric|min:0|max:100',
        ], [
            'code.unique'         => 'Kode CPL sudah ada. Gunakan kode yang berbeda.',
            'min_target.required' => 'Minimal ketercapaian wajib diisi.',
            'min_target.numeric'  => 'Minimal ketercapaian harus berupa angka.',
            'min_target.min'      => 'Minimal ketercapaian tidak boleh kurang dari 0.',
            'min_target.max'      => 'Minimal ketercapaian tidak boleh lebih dari 100.',
        ]);

        // Otomatis ikat CPL ke prodi kaprodi yang sedang login
        $validated['program_studi_id'] = $prodiId;

        Cpl::create($validated);

        return redirect()->route('cpls.index')
            ->with('success', 'CPL berhasil ditambahkan!');
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
    public function edit(Cpl $cpl)
    {
        return view('kaprodi.cpls.edit', compact('cpl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cpl $cpl)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:255|unique:obe_cpl,code,' . $cpl->id,
            'description' => 'required|string',
            'min_target'  => 'required|numeric|min:0|max:100',
        ], [
            'code.unique'         => 'Kode CPL sudah ada. Gunakan kode yang berbeda.',
            'min_target.required' => 'Minimal ketercapaian wajib diisi.',
            'min_target.numeric'  => 'Minimal ketercapaian harus berupa angka.',
            'min_target.min'      => 'Minimal ketercapaian tidak boleh kurang dari 0.',
            'min_target.max'      => 'Minimal ketercapaian tidak boleh lebih dari 100.',
        ]);

        $cpl->update($validated);

        return redirect()->route('cpls.index')
            ->with('success', 'CPL berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cpl $cpl)
    {
        $cpl->delete();

        return redirect()->route('cpls.index')
            ->with('success', 'CPL berhasil dihapus!');
    }

    public function updateMinTarget(Request $request)
    {
        $validated = $request->validate([
            'targets'   => 'required|array',
            'targets.*' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($validated['targets'] as $cplId => $value) {
            Cpl::where('id', $cplId)->update(['min_target' => $value]);
        }

        return redirect()->route('cpls.index')
            ->with('success', 'Minimal ketercapaian CPL berhasil diperbarui.');
    }
}