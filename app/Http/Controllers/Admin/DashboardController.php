<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CvmData;
use App\Models\EopData;
use App\Models\Project;
use App\Models\TcmData;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalProjects = Project::count();
        $publishedProjects = Project::where('status', 'published')->count();
        $inProgressProjects = Project::where('status', 'in_progress')->count();

        $totalEOPRecords = EopData::count();
        $totalTCMRecords = TcmData::count();
        $totalCVMRecords = CvmData::count();

        $totalUsers = User::where('is_active', true)->count();
        $totalSurveyors = User::whereHas('role', fn($q) => $q->where('slug', 'surveyor'))->count();

        $aggregatedTEV = Project::sum('tev');
        // Excludes boundary_geojson: uploaded survey-area polygons can run into
        // the megabytes, and this list only ever renders code/name/location/
        // status/tev — pulling the full geometry here made the dashboard take
        // 10+ seconds to load.
        $lastProjects = Project::latest()
            ->take(5)
            ->get(['id', 'code', 'name', 'location', 'status', 'tev']);

        $monthlyStats = $this->getMonthlyStats();

        return Inertia::render('Admin/Dashboard', [
            'totalProjects' => $totalProjects,
            'publishedProjects' => $publishedProjects,
            'inProgressProjects' => $inProgressProjects,
            'totalEOPRecords' => $totalEOPRecords,
            'totalTCMRecords' => $totalTCMRecords,
            'totalCVMRecords' => $totalCVMRecords,
            'totalUsers' => $totalUsers,
            'totalSurveyors' => $totalSurveyors,
            'aggregatedTEV' => $aggregatedTEV,
            'lastProjects' => $lastProjects,
            'monthlyStats' => $monthlyStats,
        ]);
    }

    private function getMonthlyStats()
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[$date->format('M')] = Project::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }
        return $months;
    }
}
