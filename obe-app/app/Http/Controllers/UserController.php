<?php

namespace App\Http\Controllers;

use App\Models\DosenProfile;
use App\Models\Jurusan;
use App\Models\KaprodiProfile;
use App\Models\MahasiswaProfile;
use App\Models\Pengelola;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Role-role yang dianggap "petinggi akademik" — orangnya tetap dosen,
     * hanya sedang menjabat. Saat jabatan berakhir, role dikembalikan ke 'dosen'.
     */
    private const ROLES_STRUKTURAL = ['kaprodi', 'kajur', 'dekan', 'wakil_dekan'];

    public function index(Request $request)
    {
        $role  = $request->input('role');
        $auth  = Auth::user();
        $query = User::query();

        if ($role) {
            if ($role === 'dekan') {
                $query->whereIn('role', ['dekan', 'wakil_dekan']);
            } elseif ($role === 'dosen') {
                $query->dosenAkademik();
            } else {
                $query->where('role', $role);
            }
        }

        // ── Scope per role yang sedang login ──────────────────────────────
        if ($auth) {
            if ($auth->role === 'admin_jurusan' && $auth->jurusan_id) {
                // Admin jurusan: scope per jurusan, atau per prodi kalau mode admin_prodi
                if (session('role_mode') === 'admin_prodi' && session('active_prodi_id')) {
                    $activeProdiId = (int) session('active_prodi_id');
                    if ($role === 'mahasiswa') {
                        $query->whereHas('profilMahasiswa', fn($mq) =>
                            $mq->where('program_studi_id', $activeProdiId)
                        );
                    } else {
                        $query->where('jurusan_id', $auth->jurusan_id);
                    }
                } else {
                    $query->where('jurusan_id', $auth->jurusan_id);
                }

            } elseif ($auth->role === 'kaprodi') {
                // Kaprodi: mahasiswa hanya dari prodinya, dosen dari jurusannya
                $prodiId    = $auth->activeProdiId();
                $jurusanId  = $auth->jurusan_id;

                if ($role === 'mahasiswa' && $prodiId) {
                    $query->whereHas('profilMahasiswa', fn($mq) =>
                        $mq->where('program_studi_id', $prodiId)
                    );
                } elseif (in_array($role, ['dosen', 'kajur', 'kaprodi']) && $jurusanId) {
                    $query->where('jurusan_id', $jurusanId);
                }
            }
        }

        $query->orderByRaw("CASE
                WHEN role = 'dosen'         THEN 1
                WHEN role = 'kaprodi'       THEN 2
                WHEN role = 'kajur'         THEN 3
                WHEN role = 'dekan'         THEN 4
                WHEN role = 'wakil_dekan'   THEN 5
                ELSE 6
            END ASC")
            ->orderBy('name');

        if ($role === 'mahasiswa') {
            $query->with(['profilMahasiswa.programStudi', 'jurusan']);
        } elseif ($role === 'dosen') {
            // Tab dosen: load profilKaprodi juga untuk menampilkan nama prodi kaprodi di badge jabatan
            $query->with(['profilDosen.programStudi', 'profilKaprodi.programStudi', 'pengelola', 'jurusan']);
        } elseif ($role === 'kaprodi') {
            // Tab kaprodi: load KEDUANYA — profilKaprodi (prodi yang dikepalai) & profilDosen (prodi asal akademik)
            $query->with(['profilKaprodi.programStudi', 'profilDosen.programStudi', 'jurusan']);
        } elseif ($role === 'kajur') {
            $query->with(['profilDosen.programStudi', 'jurusan']);
        } elseif ($role === 'dekan') {
            $query->with(['pengelola', 'jurusan']);
        } else {
            $query->with('jurusan');
        }

        $users = $query->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $auth = Auth::user();
        $jurusanQuery = Jurusan::orderBy('nama_jurusan');

        // Admin jurusan hanya bisa memilih jurusannya sendiri
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $jurusanQuery->where('id', $auth->jurusan_id);
        }

        $jurusan = $jurusanQuery->get();
        return view('admin.users.create', compact('jurusan'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name'       => 'required|string|max:255',
            'identity'   => 'required|string|max:255|unique:obe_pengguna,identity',
            'initials'   => 'nullable|string|max:20',
            'email'      => 'required|string|email|max:255|unique:obe_pengguna,email',
            'role'       => 'required|in:admin,admin_jurusan,kaprodi,dosen,mahasiswa,kajur,dekan,wakil_dekan,tendik,plp',
            'jurusan_id' => 'nullable|integer|exists:obe_jurusan,id',
        ];

        if ($request->input('role') === 'mahasiswa') {
            $rules['konsentrasi']      = 'required|string|exists:obe_konsentrasi,kode';
            $rules['program_studi_id'] = 'nullable|integer|exists:obe_program_studi,id';
        }

        if (in_array($request->input('role'), ['dosen', 'kaprodi', 'kajur', 'dekan', 'wakil_dekan'])) {
            $rules['program_studi_id'] = 'nullable|integer|exists:obe_program_studi,id';
        }

        // Kaprodi: wajib memilih prodi yang dikepalai
        if ($request->input('role') === 'kaprodi') {
            $rules['prodi_kepalai_id'] = 'required|integer|exists:obe_program_studi,id';
        }

        if (in_array($request->input('role'), ['dekan', 'wakil_dekan'])) {
            $rules['jabatan'] = 'required|in:dekan,wakil_dekan';
            $rules['bidang']  = 'required_if:jabatan,wakil_dekan|nullable|string';
        }

        $validated = $request->validate($rules);

        $auth = Auth::user();
        if ($auth && $auth->role === 'admin_jurusan') {
            abort_if(in_array($validated['role'], ['admin', 'admin_jurusan']), 403);
            $validated['jurusan_id'] = $auth->jurusan_id;

            // Mode admin prodi: mahasiswa baru otomatis masuk ke prodi aktif
            if (
                session('role_mode') === 'admin_prodi' &&
                session('active_prodi_id') &&
                $validated['role'] === 'mahasiswa'
            ) {
                $validated['program_studi_id'] = session('active_prodi_id');
            }
        }

        $userData             = collect($validated)->only(['name', 'identity', 'initials', 'email', 'role', 'jurusan_id'])->all();
        $userData['password'] = Hash::make($validated['identity']);

        // Jika role struktural (kaprodi/kajur/dekan/wakil_dekan), tandai jabatan_akademik = 'dosen'
        // agar status dosennya tercatat dan tidak hilang saat jabatan berakhir.
        if (in_array($validated['role'], self::ROLES_STRUKTURAL)) {
            $userData['jabatan_akademik'] = 'dosen';
        }

        $user = User::create($userData);

        $this->syncRoleProfiles($user, $validated);

        return redirect()->route('users.index', ['role' => $validated['role']])
            ->with('success', 'User berhasil ditambahkan! Password default: ' . $validated['identity']);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(User $user)
    {
        $user->loadMissing(['profilMahasiswa', 'profilDosen', 'profilKaprodi', 'pengelola', 'jurusan']);

        $auth = Auth::user();
        $jurusanQuery = Jurusan::orderBy('nama_jurusan');

        // Admin jurusan hanya bisa memilih jurusannya sendiri
        if ($auth && $auth->role === 'admin_jurusan' && $auth->jurusan_id) {
            $jurusanQuery->where('id', $auth->jurusan_id);
        }

        $jurusan = $jurusanQuery->get();

        // Prodi yang sudah terpilih (untuk pre-populate dropdown saat edit)
        $selectedProdi = null;
        $prodiList     = collect();
        if ($user->jurusan_id) {
            $prodiList = ProgramStudi::where('jurusan_id', $user->jurusan_id)
                ->orderBy('nama_prodi')->get();
        }

        // Ambil program_studi_id dari profil yang relevan (termasuk mahasiswa)
        $currentProdiId = optional($user->profilMahasiswa)->program_studi_id
            ?? optional($user->profilDosen)->program_studi_id
            ?? optional($user->profilKaprodi)->program_studi_id
            ?? null;

        return view('admin.users.edit', compact('user', 'jurusan', 'prodiList', 'currentProdiId'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'       => 'required|string|max:255',
            'identity'   => 'required|string|max:255|unique:obe_pengguna,identity,' . $user->id,
            'initials'   => 'nullable|string|max:20',
            'email'      => 'required|string|email|max:255|unique:obe_pengguna,email,' . $user->id,
            'role'       => 'required|in:admin,admin_jurusan,kaprodi,dosen,mahasiswa,kajur,dekan,wakil_dekan,tendik,plp',
            'jurusan_id' => 'nullable|integer|exists:obe_jurusan,id',
        ];

        if (in_array($request->input('role'), ['mahasiswa', $user->role]) && $user->role === 'mahasiswa') {
            $rules['konsentrasi']      = 'required|string|exists:obe_konsentrasi,kode';
            $rules['program_studi_id'] = 'nullable|integer|exists:obe_program_studi,id';
        }

        if (in_array($user->role, ['dosen', 'kaprodi', 'kajur', 'dekan', 'wakil_dekan'])) {
            $rules['program_studi_id'] = 'nullable|integer|exists:obe_program_studi,id';
        }

        // Kaprodi: wajib memilih prodi yang dikepalai
        if ($request->input('role') === 'kaprodi' || $user->role === 'kaprodi') {
            $rules['prodi_kepalai_id'] = 'required|integer|exists:obe_program_studi,id';
        }

        if (in_array($request->input('role'), ['dekan', 'wakil_dekan'])) {
            $rules['jabatan'] = 'required|in:dekan,wakil_dekan';
            $rules['bidang']  = 'required_if:jabatan,wakil_dekan|nullable|string';
        }

        $validated = $request->validate($rules);

        $auth = Auth::user();
        if ($auth && $auth->role === 'admin_jurusan') {
            $validated['jurusan_id'] = $auth->jurusan_id;
        }

        $userData = collect($validated)->only(['name', 'identity', 'initials', 'email', 'role', 'jurusan_id'])->all();

        // ── Aturan jabatan_akademik saat UPDATE ──────────────────────────
        //
        // KASUS 1: Role baru adalah struktural (kaprodi/kajur/dekan/wakil_dekan)
        //   → Pastikan jabatan_akademik = 'dosen' (tandai sebagai dosen permanen)
        //
        // KASUS 2: Role baru adalah 'dosen' (misal jabatan berakhir, dikembalikan ke dosen)
        //   → jabatan_akademik tetap 'dosen' (tidak berubah, sudah benar)
        //
        // KASUS 3: Role berubah ke non-dosen non-struktural (admin, mahasiswa, tendik, plp)
        //   → jabatan_akademik = null (bukan dosen sama sekali)
        //
        // Dengan ini, siapa pun yang pernah jadi petinggi tetap punya DosenProfile
        // dan bisa di-assign ke kelas saat jabatan berikutnya / sesudahnya.

        if (in_array($validated['role'], self::ROLES_STRUKTURAL)) {
            // Naik jabatan struktural → pastikan tanda dosen tersimpan
            $userData['jabatan_akademik'] = 'dosen';
        } elseif ($validated['role'] === 'dosen') {
            // Kembali ke dosen murni → jabatan_akademik tetap 'dosen'
            $userData['jabatan_akademik'] = 'dosen';
        } else {
            // Bukan dosen dan bukan pejabat struktural akademik
            $userData['jabatan_akademik'] = null;
        }

        $user->update($userData);

        $this->syncRoleProfiles($user, $validated);

        return redirect()->route('users.index', ['role' => $user->role])
            ->with('success', 'Data user berhasil diperbarui!');
    }

    /**
     * Sync profile records sesuai role.
     *
     * Perubahan utama vs versi lama:
     *   - Semua role struktural (kaprodi, kajur, dekan, wakil_dekan) SELALU
     *     membuat/memperbarui DosenProfile, karena mereka adalah dosen juga.
     *   - DosenProfile tidak pernah dihapus saat role berubah ke struktural.
     */
    private function syncRoleProfiles(User $user, array $validated): void
    {
        $role = $validated['role'] ?? $user->role;

        // ── Pimpinan (dekan / wakil_dekan) ──────────────────────────────
        if (in_array($role, ['dekan', 'wakil_dekan'])) {
            $jabatan = $validated['jabatan']; // 'dekan' | 'wakil_dekan'

            // Role di tabel obe_pengguna mengikuti jabatan yang dipilih
            $user->update(['role' => $jabatan]);

            Pengelola::updateOrCreate(
                ['user_id' => $user->id, 'aktif' => true],
                [
                    'jabatan' => $jabatan,
                    'bidang'  => $jabatan === 'wakil_dekan'
                                    ? ($validated['bidang'] ?? null)
                                    : null,
                    'aktif'   => true,
                ]
            );

            // Dekan/wakil_dekan juga dosen → pastikan DosenProfile ada
            DosenProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'              => $validated['identity'] ?? $user->identity,
                    'singkatan'        => $validated['initials'] ?? $user->initials,
                    'program_studi_id' => $validated['program_studi_id'] ?? null,
                ]
            );
            return;
        }

        // ── Mahasiswa ────────────────────────────────────────────────────
        if ($role === 'mahasiswa') {
            MahasiswaProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nim'              => $validated['identity'],
                    'konsentrasi'      => $validated['konsentrasi'] ?? null,
                    'program_studi_id' => $validated['program_studi_id'] ?? null,
                ]
            );
            return;
        }
        
        // ── Kaprodi ──────────────────────────────────────────────────────
        if ($role === 'kaprodi') {
            // KaprodiProfile → simpan prodi yang DIKEPALAI (jabatan struktural)
            KaprodiProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'              => $validated['identity'],
                    'singkatan'        => $validated['initials'] ?? null,
                    'program_studi_id' => $validated['prodi_kepalai_id'] ?? null,
                ]
            );

            // DosenProfile → simpan prodi ASAL sebagai dosen (identitas akademik)
            DosenProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'              => $validated['identity'],
                    'singkatan'        => $validated['initials'] ?? null,
                    'program_studi_id' => $validated['program_studi_id'] ?? null,
                ]
            );
            return;
        }

        // ── Kajur ────────────────────────────────────────────────────────
        if ($role === 'kajur') {
            // Kajur adalah dosen yang menjabat → DosenProfile wajib ada
            DosenProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'              => $validated['identity'],
                    'singkatan'        => $validated['initials'] ?? null,
                    'program_studi_id' => $validated['program_studi_id'] ?? null,
                ]
            );
            return;
        }

        // ── Dosen murni ──────────────────────────────────────────────────
        if ($role === 'dosen') {
            DosenProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'              => $validated['identity'],
                    'singkatan'        => $validated['initials'] ?? null,
                    'program_studi_id' => $validated['program_studi_id'] ?? null,
                ]
            );
        }
    }

    public function resetPassword(Request $request, User $user)
    {
        $user->update([
            'password' => Hash::make($user->identity),
        ]);

        return redirect()->route('users.edit', $user)
            ->with('success', 'Password ' . $user->name . ' berhasil direset ke default (NIP/NIM).');
    }

    public function destroy(User $user)
    {
        $role = $user->role;
        $user->delete();

        return redirect()->route('users.index', ['role' => $role])
            ->with('success', 'User berhasil dihapus!');
    }
}