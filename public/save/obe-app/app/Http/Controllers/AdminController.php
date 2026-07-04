<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $userStats = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'kaprodi' => User::where('role', 'kaprodi')->count(),
            'dosen' => User::where('role', 'dosen')->count(),
            'mahasiswa' => User::where('role', 'mahasiswa')->count(),
        ];

        return view('admin.dashboard', compact('userStats'));
    }
}
