<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Indicator;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'indicator_id' => 'required|exists:indicators,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $percentage = $request->filled('percentage') ? $validated['percentage'] : 0;
        $isAuto = !$request->filled('percentage');

        Assessment::create([
            'indicator_id' => $validated['indicator_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'percentage' => $percentage,
            'is_auto' => $isAuto
        ]);

        $this->recalculateWeights($validated['indicator_id']);

        return redirect()->back()->with('success', 'Komponen penilaian berhasil ditambahkan.');
    }

    /**
     * Update an assessment.
     */
    public function update(Request $request, Assessment $assessment)
    {
        // ... (update logic if we allowed editing percentage, but for now we haven't implemented edit form for existing items properly, 
        // the current View only allows deleting. But user might want to edit. 
        // For this task, we focus on Store/Destroy causing recalculation).
        // If we did implement update, we'd need similar logic.
        
        // Keeping it simple for now as requested.
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assessment $assessment)
    {
        $indicatorId = $assessment->indicator_id;
        $assessment->delete();
        
        $this->recalculateWeights($indicatorId);

        return redirect()->back()->with('success', 'Komponen penilaian berhasil dihapus.');
    }

    /**
     * Recalculate weights for all assessments in an indicator
     */
    private function recalculateWeights($indicatorId)
    {
        $assessments = Assessment::where('indicator_id', $indicatorId)->orderBy('id')->get();
        
        $manualAssessments = $assessments->where('is_auto', false);
        $autoAssessments = $assessments->where('is_auto', true);

        $manualTotal = $manualAssessments->sum('percentage');
        $remainingWeight = 100 - $manualTotal;
        
        // Ensure remaining weight is not negative
        if ($remainingWeight < 0) $remainingWeight = 0;

        $autoCount = $autoAssessments->count();

        if ($autoCount > 0) {
            // Calculate base split for auto items
            $base = floor(($remainingWeight / $autoCount) * 100) / 100;
            $remainder = round($remainingWeight - ($base * $autoCount), 2);

            $i = 0;
            foreach ($autoAssessments as $assessment) {
                $newWeight = $base;
                
                // Add remainder to the last auto item
                if ($i === $autoCount - 1) {
                    $newWeight += $remainder;
                }

                $assessment->update(['percentage' => $newWeight]);
                $i++;
            }
        }
    }
}
