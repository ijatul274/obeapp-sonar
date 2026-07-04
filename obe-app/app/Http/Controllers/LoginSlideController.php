<?php

namespace App\Http\Controllers;

use App\Models\LoginSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LoginSlideController extends Controller
{
    public function index()
    {
        $slides = LoginSlide::ordered()->get();
        return view('admin.login-slides.index', compact('slides'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image'      => 'required|image|max:4096',
            'title'      => 'nullable|string|max:255',
            'caption'    => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $path = $request->file('image')->store('login-slides', 'public');

        LoginSlide::create([
            'image_path' => $path,
            'title'      => $validated['title']      ?? null,
            'caption'    => $validated['caption']    ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('admin.login-slides.index')->with('success', 'Slide berhasil ditambahkan.');
    }

    public function update(Request $request, LoginSlide $loginSlide)
    {
        $validated = $request->validate([
            'image'      => 'nullable|image|max:4096',
            'title'      => 'nullable|string|max:255',
            'caption'    => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $data = [
            'title'      => $validated['title']      ?? null,
            'caption'    => $validated['caption']    ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active'  => (bool) ($validated['is_active'] ?? false),
        ];

        if ($request->hasFile('image')) {
            // hapus gambar lama jika file lokal
            if ($loginSlide->image_path && !preg_match('#^https?://#i', $loginSlide->image_path)) {
                Storage::disk('public')->delete($loginSlide->image_path);
            }
            $data['image_path'] = $request->file('image')->store('login-slides', 'public');
        }

        $loginSlide->update($data);

        return redirect()->route('admin.login-slides.index')->with('success', 'Slide berhasil diperbarui.');
    }

    public function destroy(LoginSlide $loginSlide)
    {
        if ($loginSlide->image_path && !preg_match('#^https?://#i', $loginSlide->image_path)) {
            Storage::disk('public')->delete($loginSlide->image_path);
        }
        $loginSlide->delete();

        return redirect()->route('admin.login-slides.index')->with('success', 'Slide berhasil dihapus.');
    }
}
