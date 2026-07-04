<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Cpmk;
use App\Models\User;
use Illuminate\Http\Request;

class CpmkController extends Controller
{
    // ─── CONST ───────────────────────────────────────────────────────────────
    const TOTAL_MEETINGS = 16;

    /**
     * Recalculate meeting_start / meeting_end for all CPMKs of a course,
     * ordered by their id (creation order).
     */
    private function recalculateMeetings(int $courseId): void
    {
        $cpmks = Cpmk::where('course_id', $courseId)->orderBy('id')->get();
        $current = 1;
        foreach ($cpmks as $cpmk) {
            $count = (int) round(($cpmk->percentage / 100) * self::TOTAL_MEETINGS);
            $count = max($count, 1);
            $cpmk->meeting_start = $current;
            $cpmk->meeting_end   = $current + $count - 1;
            $cpmk->save();
            $current += $count;
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        // Form sekarang berupa modal di halaman detail mata kuliah.
        return redirect()->route('courses.show', $course);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'               => 'required|exists:obe_mata_kuliah,id',
            'cpl_id'                  => 'required|exists:obe_cpl,id',
            'code'                    => 'required|string',
            'description'             => 'required|string',
            'lecturer_id'             => 'nullable|exists:obe_pengguna,id',
            'percentage'              => 'required|numeric|min:0|max:100',
            'indicator_weight_type'   => 'required|in:otomatis,manual',
            'indicator_descriptions'  => 'nullable|array',
            'indicator_descriptions.*'=> 'nullable|string',
            'indicator_percentages'   => 'nullable|array',
            'indicator_percentages.*' => 'nullable|numeric|min:0|max:100',
        ]);

        // Create CPMK
        $cpmk = Cpmk::create([
            'course_id'   => $validated['course_id'],
            'cpl_id'      => $validated['cpl_id'],
            'code'        => $validated['code'],
            'description' => $validated['description'],
            'lecturer_id' => $validated['lecturer_id'] ?? null,
            'percentage'  => $validated['percentage'],
        ]);

        $this->saveIndicators($cpmk, $validated);

        // Recalculate meeting ranges for all CPMKs of this course
        $this->recalculateMeetings((int) $validated['course_id']);

        return redirect()->route('courses.show', $validated['course_id'])->with('success', 'CPMK berhasil ditambahkan.');
    }

    /**
     * Simpan indikator dengan dukungan mode otomatis, manual, dan campuran.
     * Mode otomatis  : semua indikator dibagi rata 100/N.
     * Mode manual    : nilai persentase diambil dari input; entry kosong
     *                  diperlakukan sebagai auto-remainder dari (100 - manualTotal).
     */
    private function saveIndicators(Cpmk $cpmk, array $validated): void
    {
        if (empty($validated['indicator_descriptions'])) {
            return;
        }

        $rawDescs = $validated['indicator_descriptions'];
        $rawPcts  = $validated['indicator_percentages'] ?? [];

        // Pasangkan deskripsi & persentase berdasar index, lalu buang baris kosong.
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
            // Bagi rata 100/N untuk semua indikator
            $per = round(100 / $count, 2);
            foreach ($rows as $r) {
                $cpmk->indicators()->create(['description' => $r['desc'], 'percentage' => $per]);
            }
            return;
        }

        // Manual / campuran: hitung auto-remainder untuk yang null
        $autoRows    = array_filter($rows, fn($r) => $r['pct'] === null);
        $manualTotal = array_sum(array_column(array_filter($rows, fn($r) => $r['pct'] !== null), 'pct'));
        $autoCount   = count($autoRows);
        $autoEach    = $autoCount > 0 ? max(0, round((100 - $manualTotal) / $autoCount, 2)) : 0;

        foreach ($rows as $r) {
            $cpmk->indicators()->create([
                'description' => $r['desc'],
                'percentage'  => $r['pct'] ?? $autoEach,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cpmk $cpmk)
    {
        // Form sekarang berupa modal di halaman detail mata kuliah.
        return redirect()->route('courses.show', $cpmk->course_id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cpmk $cpmk)
    {
        $validated = $request->validate([
            'cpl_id'                  => 'required|exists:obe_cpl,id',
            'code'                    => 'required|string',
            'description'             => 'required|string',
            'lecturer_id'             => 'nullable|exists:obe_pengguna,id',
            'percentage'              => 'required|numeric|min:0|max:100',
            'indicator_weight_type'   => 'required|in:otomatis,manual',
            'indicator_descriptions'  => 'nullable|array',
            'indicator_descriptions.*'=> 'nullable|string',
            'indicator_percentages'   => 'nullable|array',
            'indicator_percentages.*' => 'nullable|numeric|min:0|max:100',
        ]);

        // Update CPMK
        $cpmk->update([
            'cpl_id'      => $validated['cpl_id'],
            'code'        => $validated['code'],
            'description' => $validated['description'],
            'lecturer_id' => $validated['lecturer_id'] ?? null,
            'percentage'  => $validated['percentage'],
        ]);

        $cpmk->indicators()->delete();
        $this->saveIndicators($cpmk, $validated);

        // Recalculate meeting ranges for all CPMKs of this course
        $this->recalculateMeetings($cpmk->course_id);

        return redirect()->route('courses.show', $cpmk->course_id)->with('success', 'CPMK berhasil diperbarui.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cpmk $cpmk)
    {
        $cpmk->load(['course', 'cpl', 'lecturer', 'indicators']);
        return view('kaprodi.cpmks.show', compact('cpmk'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cpmk $cpmk)
    {
        $courseId = $cpmk->course_id;
        $cpmk->delete();

        // Recalculate meeting ranges after deletion
        $this->recalculateMeetings($courseId);

        return redirect()->route('courses.show', $courseId)->with('success', 'CPMK berhasil dihapus.');
    }
}
