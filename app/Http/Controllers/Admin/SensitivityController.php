<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SensitivityController extends Controller
{
    public function index(): Response
    {
        $projects = Project::where('status', '!=', 'draft')
            ->whereHas('benefits')
            ->whereHas('costs')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Sensitivity/Index', ['projects' => $projects]);
    }

    public function simulate(Request $request)
    {
        $project = Project::with(['benefits', 'costs'])->findOrFail($request->project_id);

        $inflationRate = $request->input('inflation_rate', 0) / 100;
        $priceAdjustment = $request->input('price_adjustment', 0) / 100;
        $discountRate = $request->input('discount_rate', 0) / 100;

        $originalBenefits = (float) $project->benefits->sum('value');
        $originalCosts = (float) $project->costs->sum('value');
        $originalTEV = $originalBenefits - $originalCosts;
        $originalBCR = $originalCosts > 0 ? $originalBenefits / $originalCosts : 0;

        $adjustedBenefits = $originalBenefits * (1 + $priceAdjustment);
        $adjustedCosts = $originalCosts * (1 + $inflationRate);

        if ($discountRate > 0) {
            $adjustedBenefits = $adjustedBenefits / (1 + $discountRate);
            $adjustedCosts = $adjustedCosts / (1 + $discountRate);
        }

        $adjustedTEV = $adjustedBenefits - $adjustedCosts;
        $adjustedBCR = $adjustedCosts > 0 ? $adjustedBenefits / $adjustedCosts : 0;

        return response()->json([
            'original' => [
                'tev' => round($originalTEV, 2),
                'benefits' => round($originalBenefits, 2),
                'costs' => round($originalCosts, 2),
                'bcr' => round($originalBCR, 4),
            ],
            'adjusted' => [
                'tev' => round($adjustedTEV, 2),
                'benefits' => round($adjustedBenefits, 2),
                'costs' => round($adjustedCosts, 2),
                'bcr' => round($adjustedBCR, 4),
            ],
            'changes' => [
                'tev_pct' => $originalTEV != 0 ? round(($adjustedTEV - $originalTEV) / abs($originalTEV) * 100, 2) : 0,
                'bcr_pct' => $originalBCR != 0 ? round(($adjustedBCR - $originalBCR) / $originalBCR * 100, 2) : 0,
            ],
        ]);
    }
}
