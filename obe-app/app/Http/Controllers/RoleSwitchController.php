<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    /**
     * Role struktural yang juga merupakan dosen akademik bisa switch
     * tampilan ke mode dosen tanpa logout.
     * Mode disimpan di session: 'role_mode' => '<role_asli>' | 'dosen'
     */
    private const BISA_SWITCH = ['kaprodi', 'kajur', 'dekan', 'wakil_dekan'];

    public function switch(Request $request)
    {
        $user = Auth::user();

        // ── Mode Admin Prodi: khusus admin_jurusan ───────────────────
        if ($user->role === 'admin_jurusan') {
            $mode    = $request->input('mode');     // 'admin_jurusan' | 'admin_prodi'
            $prodiId = $request->input('prodi_id'); // hanya saat mode admin_prodi

            if ($mode === 'admin_prodi') {
                // Validasi prodi milik jurusan ini
                $prodi = ProgramStudi::where('id', $prodiId)
                    ->where('jurusan_id', $user->jurusan_id)
                    ->firstOrFail();

                session([
                    'role_mode'      => 'admin_prodi',
                    'active_prodi_id'=> $prodi->id,
                    'active_prodi_nama'=> $prodi->nama_prodi,
                ]);
            } else {
                // Kembali ke mode admin jurusan
                session()->forget(['role_mode', 'active_prodi_id', 'active_prodi_nama']);
            }

            return redirect()->route('admin.dashboard');
        }

        // ── Mode Dosen Switch: kaprodi / kajur / dekan / wakil_dekan ─
        if (!in_array($user->role, self::BISA_SWITCH)) {
            abort(403, 'Role ini tidak dapat berganti mode tampilan.');
        }

        $to = $request->input('mode'); // 'dosen' atau role aslinya

        $validModes = ['dosen', $user->role];
        if (!in_array($to, $validModes)) {
            abort(422, 'Mode tidak valid.');
        }

        session(['role_mode' => $to]);

        return $to === 'dosen'
            ? redirect()->route('dosen.dashboard')
            : redirect()->route($this->dashboardRoute($user->role));
    }

    private function dashboardRoute(string $role): string
    {
        return match($role) {
            'kaprodi'    => 'kaprodi.dashboard',
            'kajur'      => 'kaprodi.dashboard',
            'dekan',
            'wakil_dekan'=> 'dosen.dashboard',
            default      => 'dosen.dashboard',
        };
    }
}