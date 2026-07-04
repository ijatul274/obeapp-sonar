<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'identity' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt(['identity' => $credentials['identity'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'identity' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Role yang bisa switch ke mode dosen — reset session ke role aslinya tiap login baru
        $bisaSwitch = ['kaprodi', 'kajur', 'dekan', 'wakil_dekan'];
        if (in_array($user->role, $bisaSwitch)) {
            session(['role_mode' => $user->role]);
        }

        return match(true) {
            in_array($user->role, ['admin', 'admin_jurusan'])        => redirect()->intended('admin/dashboard'),
            $user->role === 'kaprodi'                                => redirect()->intended('kaprodi/dashboard'),
            in_array($user->role, ['kajur'])                         => redirect()->intended('kaprodi/dashboard'),
            in_array($user->role, ['dosen', 'dekan', 'wakil_dekan']) => redirect()->intended('dosen/pemetaan'),
            $user->role === 'mahasiswa'                              => redirect()->intended('mahasiswa/dashboard'),
            default                                                  => redirect()->intended('dashboard'),
        };
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}