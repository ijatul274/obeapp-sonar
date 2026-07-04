<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'identity' => ['required', 'string'], // NIP or NIM
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['identity' => $credentials['identity'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            return match ($user->role) {
                'admin' => redirect()->intended('admin/dashboard'),
                'kaprodi' => redirect()->intended('kaprodi/dashboard'),
                'dosen' => redirect()->intended('dosen/dashboard'),
                'mahasiswa' => redirect()->intended('mahasiswa/dashboard'),
                default => redirect()->intended('dashboard'),
            };
        }

        throw ValidationException::withMessages([
            'identity' => trans('auth.failed'),
        ]);
    }
    
    /**
     * Log the user out of the application.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
