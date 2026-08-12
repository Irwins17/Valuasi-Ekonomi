<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectValuationSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Editing a project's discounting assumptions.
 *
 * Saving here re-runs the project's totals immediately: a discount rate that
 * has changed but a TEV that has not would be worse than either, because the
 * page would be showing a figure computed under assumptions it no longer
 * displays.
 */
class ProjectValuationSettingController extends Controller
{
    public function update(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'base_year' => ['required', 'integer', 'min:1900', 'max:2200'],
            // A negative rate is unusual but legitimate in some appraisals, so
            // it is allowed; -100% or below is not, because the discount
            // factor (1 + r) would hit zero and the division is undefined.
            'discount_rate' => ['required', 'numeric', 'gt:-100', 'max:100'],
            'analysis_period' => ['required', 'integer', 'min:1', 'max:200'],
            'currency' => ['required', 'string', 'max:8'],
            'start_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'end_year' => ['nullable', 'integer', 'min:1900', 'max:2200', 'gte:start_year'],
            'eop_value_basis' => ['required', Rule::in(array_keys(ProjectValuationSetting::EOP_VALUE_BASES))],
        ], [
            'discount_rate.gt' => 'Discount rate harus lebih besar dari -100%.',
            'end_year.gte' => 'Tahun akhir tidak boleh sebelum tahun mulai.',
        ], [
            'base_year' => 'tahun dasar',
            'discount_rate' => 'discount rate',
            'analysis_period' => 'periode analisis',
        ]);

        ProjectValuationSetting::updateOrCreate(
            ['project_id' => $project->id],
            [...$validated, 'updated_by' => auth()->id()],
        );

        // Totals depend on these assumptions, so they are recomputed rather
        // than left stale until someone happens to press "Hitung TEV".
        $project->load('valuationSetting')->calculateTEV()->save();

        return back()->with('success', 'Setting valuasi disimpan dan TEV dihitung ulang.');
    }
}
