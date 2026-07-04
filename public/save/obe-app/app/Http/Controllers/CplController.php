<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;

class CplController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cpls = Cpl::paginate(10);
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
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:cpls,code',
            'description' => 'required|string',
        ], [
            'code.unique' => 'Kode CPL sudah ada. Gunakan kode yang berbeda.',
        ]);

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
            'code' => 'required|string|max:255|unique:cpls,code,' . $cpl->id,
            'description' => 'required|string',
        ], [
            'code.unique' => 'Kode CPL sudah ada. Gunakan kode yang berbeda.',
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
}
