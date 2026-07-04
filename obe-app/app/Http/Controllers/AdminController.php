<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $auth       = Auth::user();
        $isAdminJur = $auth && $auth->role === 'admin_jurusan';
        $isKaprodi  = $auth && $auth->role === 'kaprodi';
        $jurusanId  = ($isAdminJur || $isKaprodi) ? $auth->jurusan_id : null;

        // Mode admin prodi: admin jurusan sedang login sebagai admin prodi tertentu
        $isAdminProdi  = $isAdminJur && session('role_mode') === 'admin_prodi';
        $activeProdiId = $isAdminProdi
            ? session('active_prodi_id')
            : ($isKaprodi ? $auth->activeProdiId() : null);

        $countQ = function (?string $r = null) use ($isAdminJur, $isKaprodi, $jurusanId, $isAdminProdi, $activeProdiId) {
            $q = User::query();

            if ($r === 'dosen') {
                $q->dosenAkademik();
            } elseif ($r) {
                $q->where('role', $r);
            }

            if ($isAdminProdi && $activeProdiId) {
                // Admin prodi: mahasiswa hanya dari prodi aktif
                if ($r === 'mahasiswa') {
                    $q->whereHas('profilMahasiswa', fn($mq) => $mq->where('program_studi_id', $activeProdiId));
                } else {
                    if ($jurusanId) $q->where('jurusan_id', $jurusanId);
                }
            } elseif ($isKaprodi && $jurusanId) {
                // Kaprodi: mahasiswa dari prodinya, dosen dari jurusannya
                if ($r === 'mahasiswa' && $activeProdiId) {
                    $q->whereHas('profilMahasiswa', fn($mq) => $mq->where('program_studi_id', $activeProdiId));
                } elseif ($r !== 'mahasiswa') {
                    $q->where('jurusan_id', $jurusanId);
                }
            } elseif ($isAdminJur && $jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            }

            return $q->count();
        };

        // Hitung mahasiswa sekali, reuse untuk total agar tidak query 2x
        $mahasiswaCount = $countQ('mahasiswa');

        $userStats = [
            // Mode admin prodi: total = hanya mahasiswa prodi aktif
            'total'         => $isAdminProdi ? $mahasiswaCount : $countQ(),
            'admin'         => $countQ('admin'),
            'admin_jurusan' => $countQ('admin_jurusan'),
            'kajur'         => $countQ('kajur'),
            'kaprodi'       => $countQ('kaprodi'),
            'dosen'         => $countQ('dosen'),
            'tendik'        => $countQ('tendik'),
            'plp'           => $countQ('plp'),
            'mahasiswa'     => $mahasiswaCount,
        ];

        // Prodi milik jurusan ini (untuk tombol switch di dashboard admin jurusan)
        $prodiJurusan = $isAdminJur && $jurusanId
            ? ProgramStudi::where('jurusan_id', $jurusanId)->orderBy('nama_prodi')->get()
            : collect();

        return view('admin.dashboard', compact('userStats', 'prodiJurusan'));
    }
}