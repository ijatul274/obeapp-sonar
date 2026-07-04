<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassroomCpmk;
use App\Models\ClassroomCpmkAssessment;
use App\Models\ClassroomCpmkAssessmentScore;
use App\Models\ClassroomCpmkIndicator;
use App\Models\Cpl;
use App\Models\User;
use App\Notifications\CpmkStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClassroomCpmkController extends Controller
{
    const TOTAL_MEETINGS = 16;

    /**
     * Simpan indikator dengan dukungan mode otomatis, manual, dan campuran.
     * Mode otomatis : semua indikator dibagi rata 100/N.
     * Mode manual   : nilai persentase diambil dari input; entry kosong
     *                 diperlakukan sebagai auto-remainder dari (100 - manualTotal).
     */
    private function saveIndicators(ClassroomCpmk $cpmk, array $validated): void
    {
        if (empty($validated['indicator_descriptions'])) {
            return;
        }

        $rawDescs = $validated['indicator_descriptions'];
        $rawPcts  = $validated['indicator_percentages'] ?? [];

        $rows = [];
        foreach ($rawDescs as $i => $desc) {
            $desc = is_string($desc) ? trim($desc) : '';
            if ($desc === '') continue;

            $pctRaw = $rawPcts[$i] ?? null;
            $hasPct = $pctRaw !== null && $pctRaw !== '' && is_numeric($pctRaw);
            $rows[] = ['desc' => $desc, 'pct' => $hasPct ? (float) $pctRaw : null];
        }

        $count = count($rows);
        if ($count === 0) return;

        if (($validated['indicator_weight_type'] ?? 'otomatis') === 'otomatis') {
            $per = round(100 / $count, 2);
            foreach ($rows as $r) {
                $cpmk->indicators()->create(['description' => $r['desc'], 'percentage' => $per]);
            }
            return;
        }

        $manualTotal = array_sum(array_column(array_filter($rows, fn($r) => $r['pct'] !== null), 'pct'));
        $autoCount   = count(array_filter($rows, fn($r) => $r['pct'] === null));
        $autoEach    = $autoCount > 0 ? max(0, round((100 - $manualTotal) / $autoCount, 2)) : 0;

        foreach ($rows as $r) {
            $cpmk->indicators()->create([
                'description' => $r['desc'],
                'percentage'  => $r['pct'] ?? $autoEach,
            ]);
        }
    }

    /* ─── Recalculate meeting ranges ──────────────────────── */
    private function recalculateMeetings(int $classroomId): void
    {
        $cpmks   = ClassroomCpmk::where('classroom_id', $classroomId)->orderBy('id')->get();
        $current = 1;
        foreach ($cpmks as $cpmk) {
            $count            = (int) round(($cpmk->percentage / 100) * self::TOTAL_MEETINGS);
            $count            = max($count, 1);
            $cpmk->meeting_start = $current;
            $cpmk->meeting_end   = $current + $count - 1;
            $cpmk->save();
            $current += $count;
        }
    }

    /* ─── Create form ─────────────────────────────────────── */
    public function create(Classroom $classroom)
    {
        $user = Auth::user();

        // Pastikan dosen ditugaskan di kelas ini
        abort_unless(
            $classroom->cpmks()->wherePivot('lecturer_id', $user->id)->exists()
            || $classroom->lecturer_id === $user->id,
            403
        );

        $cpls = Cpl::orderBy('code')->get();

        // Ambil template CPMK dari mata kuliah (data lama sebagai referensi)
        $templates = $classroom->course?->cpmks()->with('cpl')->get() ?? collect();

        return view('dosen.classroom-cpmks.create', compact('classroom', 'cpls', 'templates'));
    }

    /* ─── Store ───────────────────────────────────────────── */
    public function store(Request $request, Classroom $classroom)
    {
        $user = Auth::user();

        abort_unless(
            $classroom->cpmks()->wherePivot('lecturer_id', $user->id)->exists()
            || $classroom->lecturer_id === $user->id,
            403
        );

        $validated = $request->validate([
            'cpl_id'                   => 'required|exists:cpls,id',
            'code'                     => 'required|string|max:50',
            'description'              => 'required|string',
            'percentage'               => 'required|numeric|min:0|max:100',
            'indicator_weight_type'    => 'required|in:otomatis,manual',
            'indicator_descriptions'   => 'nullable|array',
            'indicator_descriptions.*' => 'nullable|string',
            'indicator_percentages'    => 'nullable|array',
            'indicator_percentages.*'  => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Persetujuan Kaprodi dihapus — CPMK langsung approved.
            $cpmk = ClassroomCpmk::create([
                'classroom_id' => $classroom->id,
                'cpl_id'       => $validated['cpl_id'],
                'created_by'   => $user->id,
                'code'         => $validated['code'],
                'description'  => $validated['description'],
                'percentage'   => $validated['percentage'],
                'status'       => 'approved',
                'approved_at'  => now(),
            ]);

            $this->saveIndicators($cpmk, $validated);

            $this->recalculateMeetings($classroom->id);
            DB::commit();

            return redirect()
                ->route('dosen.classrooms.show', $classroom)
                ->with('success', 'CPMK berhasil disimpan dan langsung aktif.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan CPMK: ' . $e->getMessage());
        }
    }

    /* ─── Edit form ───────────────────────────────────────── */
    public function edit(ClassroomCpmk $classroomCpmk)
    {
        $user = Auth::user();

        abort_unless($classroomCpmk->created_by === $user->id, 403);
        abort_unless($classroomCpmk->isEditable(), 403, 'CPMK ini tidak dapat diedit karena sudah di-submit atau disetujui.');

        $classroomCpmk->load('indicators', 'classroom', 'cpl');
        $cpls      = Cpl::orderBy('code')->get();
        $classroom = $classroomCpmk->classroom;

        return view('dosen.classroom-cpmks.edit', compact('classroomCpmk', 'cpls', 'classroom'));
    }

    /* ─── Update ──────────────────────────────────────────── */
    public function update(Request $request, ClassroomCpmk $classroomCpmk)
    {
        $user = Auth::user();
        abort_unless($classroomCpmk->created_by === $user->id, 403);
        abort_unless($classroomCpmk->isEditable(), 403);

        $validated = $request->validate([
            'cpl_id'                   => 'required|exists:cpls,id',
            'code'                     => 'required|string|max:50',
            'description'              => 'required|string',
            'percentage'               => 'required|numeric|min:0|max:100',
            'indicator_weight_type'    => 'required|in:otomatis,manual',
            'indicator_descriptions'   => 'nullable|array',
            'indicator_descriptions.*' => 'nullable|string',
            'indicator_percentages'    => 'nullable|array',
            'indicator_percentages.*'  => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $classroomCpmk->update([
                'cpl_id'      => $validated['cpl_id'],
                'code'        => $validated['code'],
                'description' => $validated['description'],
                'percentage'  => $validated['percentage'],
                'status'      => 'approved',
                'approved_at' => now(),
                'rejection_note' => null,
            ]);

            // Hapus indikator lama lalu buat ulang
            $classroomCpmk->indicators()->delete();
            $this->saveIndicators($classroomCpmk, $validated);

            $this->recalculateMeetings($classroomCpmk->classroom_id);
            DB::commit();

            return redirect()
                ->route('dosen.classrooms.show', $classroomCpmk->classroom_id)
                ->with('success', 'CPMK berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /* ─── Delete ──────────────────────────────────────────── */
    public function destroy(ClassroomCpmk $classroomCpmk)
    {
        $user = Auth::user();
        abort_unless($classroomCpmk->created_by === $user->id, 403);
        abort_unless($classroomCpmk->isEditable(), 403);

        $classroomId = $classroomCpmk->classroom_id;
        $classroomCpmk->delete();
        $this->recalculateMeetings($classroomId);

        return redirect()
            ->route('dosen.classrooms.show', $classroomId)
            ->with('success', 'CPMK berhasil dihapus.');
    }

    /* ─── Submit for Approval ─────────────────────────────── */
    public function submitApproval(ClassroomCpmk $classroomCpmk)
    {
        $user = Auth::user();
        abort_unless($classroomCpmk->created_by === $user->id, 403);
        abort_unless($classroomCpmk->isEditable(), 403, 'CPMK sudah disubmit atau disetujui.');

        $classroomCpmk->update(['status' => 'pending']);

        // Notifikasi semua Kaprodi
        $kaprodiUsers = User::where('role', 'kaprodi')->get();
        foreach ($kaprodiUsers as $kaprodi) {
            $kaprodi->notify(new CpmkStatusChanged($classroomCpmk, 'submitted'));
        }

        return redirect()
            ->route('dosen.classrooms.show', $classroomCpmk->classroom_id)
            ->with('success', 'CPMK telah dikirim untuk persetujuan Kaprodi.');
    }

    /* ─── Store Assessments (Komponen Penilaian per Indikator) ─── */
    public function storeAssessments(Request $request, ClassroomCpmkIndicator $indicator)
    {
        $validated = $request->validate([
            'components'             => 'required|array|min:1',
            'components.*.nama'      => 'required|string|max:255',
            'components.*.deskripsi' => 'nullable|string',
            'components.*.bobotType' => 'required|in:otomatis,manual',
            'components.*.bobot'     => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $indicator->assessments()->delete();

            foreach ($validated['components'] as $comp) {
                $isAuto = $comp['bobotType'] === 'otomatis';
                $indicator->assessments()->create([
                    'name'        => $comp['nama'],
                    'description' => $comp['deskripsi'] ?? null,
                    'percentage'  => $isAuto ? 0 : (float) $comp['bobot'],
                    'is_auto'     => $isAuto,
                ]);
            }

            // Recalculate auto weights
            $assessments = $indicator->assessments()->orderBy('id')->get();
            $manualTotal = $assessments->where('is_auto', false)->sum('percentage');
            $autoItems   = $assessments->where('is_auto', true);
            $autoCount   = $autoItems->count();

            if ($autoCount > 0) {
                $remaining = max(0, 100 - $manualTotal);
                $base      = floor(($remaining / $autoCount) * 100) / 100;
                $remainder = round($remaining - ($base * $autoCount), 2);
                $i = 0;
                foreach ($autoItems as $a) {
                    $a->update(['percentage' => $base + ($i === $autoCount - 1 ? $remainder : 0)]);
                    $i++;
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Komponen penilaian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /* ─── Store Score (Nilai Mahasiswa) ───────────────────── */
    public function storeScores(Request $request, ClassroomCpmkAssessment $assessment)
    {
        $validated = $request->validate([
            'scores'            => 'required|array',
            'scores.*.student_id' => 'required|exists:users,id',
            'scores.*.score'    => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['scores'] as $item) {
                ClassroomCpmkAssessmentScore::updateOrCreate(
                    [
                        'classroom_cpmk_assessment_id' => $assessment->id,
                        'student_id'                   => $item['student_id'],
                    ],
                    ['score' => $item['score']]
                );
            }
            DB::commit();
            return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }
}
