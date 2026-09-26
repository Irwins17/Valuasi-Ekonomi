<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BenefitController;
use App\Http\Controllers\Admin\BoundaryLookupController;
use App\Http\Controllers\Admin\CostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnvironmentalCoefficientController;
use App\Http\Controllers\Admin\MarketPriceController;
use App\Http\Controllers\Admin\Modules\AbmController;
use App\Http\Controllers\Admin\Modules\AdcController;
use App\Http\Controllers\Admin\Modules\BtmController;
use App\Http\Controllers\Admin\Modules\CeController;
use App\Http\Controllers\Admin\Modules\CvmAnalysisController;
use App\Http\Controllers\Admin\Modules\CvmController;
use App\Http\Controllers\Admin\Modules\HpmController;
use App\Http\Controllers\Admin\Modules\DuvController;
use App\Http\Controllers\Admin\Modules\EcosystemServiceController;
use App\Http\Controllers\Admin\Modules\EopController;
use App\Http\Controllers\Admin\Modules\RcmController;
use App\Http\Controllers\Admin\Modules\TcmAnalysisController;
use App\Http\Controllers\Admin\Modules\TcmController;
use App\Http\Controllers\Admin\Modules\ValuationModuleController;
use App\Http\Controllers\Admin\ProjectAssumptionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectValuationSettingController;
use App\Http\Controllers\Admin\SensitivityController;
use App\Http\Controllers\Admin\StakeholderValidationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\LandingController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/dashboard-publik', [LandingController::class, 'dashboard'])->name('public.dashboard');
Route::get('/glossary', [LandingController::class, 'glossary'])->name('public.glossary');
Route::get('/project/{id}', [LandingController::class, 'projectDetail'])->name('public.project');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes (Protected) — RBAC matrix: admin has full access everywhere;
// surveyor can view + enter field data (Projects view, EOP/TCM/CVM manage);
// analyst can view + run sensitivity analysis (verification/reporting).
Route::middleware(['auth'])->group(function () {
    // Dashboard — available to every authenticated role
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects — viewable by all roles, mutated by admin only.
    // NOTE: static segments (/create) must stay registered before the /{id}
    // wildcard, or Laravel matches "create" as $id and 404s on findOrFail().
    Route::prefix('/admin/projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('admin.projects.index');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/create', [ProjectController::class, 'create'])->name('admin.projects.create');
            Route::post('/', [ProjectController::class, 'store'])->name('admin.projects.store');
        });

        Route::middleware(['role:admin'])->get('/boundary-lookup', [BoundaryLookupController::class, 'lookup'])->name('admin.boundary.lookup');

        Route::get('/{id}', [ProjectController::class, 'show'])->name('admin.projects.show');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/{id}/edit', [ProjectController::class, 'edit'])->name('admin.projects.edit');
            Route::put('/{id}', [ProjectController::class, 'update'])->name('admin.projects.update');
            Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');
            Route::post('/{id}/calculate-tev', [ProjectController::class, 'calculateTEV'])->name('admin.projects.calculateTEV');
            Route::put('/{id}/valuation-settings', [ProjectValuationSettingController::class, 'update'])->name('admin.projects.settings.update');
            Route::get('/{id}/export', [ProjectController::class, 'export'])->name('admin.projects.export');
        });
    });

    // Benefits & Costs — admin only (manual valuation-result entry, not field data)
    Route::middleware(['role:admin'])->prefix('/admin/projects/{projectId}')->group(function () {
        Route::get('/benefits/create', [BenefitController::class, 'create'])->name('admin.benefits.create');
        Route::post('/benefits', [BenefitController::class, 'store'])->name('admin.benefits.store');
        Route::get('/benefits/{benefitId}/edit', [BenefitController::class, 'edit'])->name('admin.benefits.edit');
        Route::put('/benefits/{benefitId}', [BenefitController::class, 'update'])->name('admin.benefits.update');
        Route::delete('/benefits/{benefitId}', [BenefitController::class, 'destroy'])->name('admin.benefits.destroy');

        Route::get('/costs/create', [CostController::class, 'create'])->name('admin.costs.create');
        Route::post('/costs', [CostController::class, 'store'])->name('admin.costs.store');
        Route::get('/costs/{costId}/edit', [CostController::class, 'edit'])->name('admin.costs.edit');
        Route::put('/costs/{costId}', [CostController::class, 'update'])->name('admin.costs.update');
        Route::delete('/costs/{costId}', [CostController::class, 'destroy'])->name('admin.costs.destroy');
    });

    // Uji Asumsi & Validasi Stakeholder (Langkah 9) — admin + analyst, same
    // access as Sensitivity Analysis since these are QA/verification steps.
    Route::middleware(['role:admin,analyst'])->prefix('/admin/projects/{projectId}')->group(function () {
        Route::get('/assumptions', [ProjectAssumptionController::class, 'index'])->name('admin.assumptions.index');
        Route::get('/assumptions/create', [ProjectAssumptionController::class, 'create'])->name('admin.assumptions.create');
        Route::post('/assumptions', [ProjectAssumptionController::class, 'store'])->name('admin.assumptions.store');
        Route::get('/assumptions/{id}/edit', [ProjectAssumptionController::class, 'edit'])->name('admin.assumptions.edit');
        Route::put('/assumptions/{id}', [ProjectAssumptionController::class, 'update'])->name('admin.assumptions.update');
        Route::delete('/assumptions/{id}', [ProjectAssumptionController::class, 'destroy'])->name('admin.assumptions.destroy');

        Route::get('/stakeholder-validations', [StakeholderValidationController::class, 'index'])->name('admin.stakeholder-validations.index');
        Route::get('/stakeholder-validations/create', [StakeholderValidationController::class, 'create'])->name('admin.stakeholder-validations.create');
        Route::post('/stakeholder-validations', [StakeholderValidationController::class, 'store'])->name('admin.stakeholder-validations.store');
        Route::get('/stakeholder-validations/{id}/edit', [StakeholderValidationController::class, 'edit'])->name('admin.stakeholder-validations.edit');
        Route::put('/stakeholder-validations/{id}', [StakeholderValidationController::class, 'update'])->name('admin.stakeholder-validations.update');
        Route::delete('/stakeholder-validations/{id}', [StakeholderValidationController::class, 'destroy'])->name('admin.stakeholder-validations.destroy');
    });

    // Module registry — the "Modul Valuasi" list plus custom module CRUD.
    // Registered before the per-method groups below; its only wildcard route
    // requires a literal "configure"/verb match, so it cannot shadow them.
    Route::prefix('/admin/projects/{projectId}/modules')->group(function () {
        Route::get('/', [ValuationModuleController::class, 'index'])->name('admin.modules.index');

        Route::middleware(['role:admin'])->group(function () {
            Route::get('/create', [ValuationModuleController::class, 'create'])->name('admin.modules.create');
            Route::post('/', [ValuationModuleController::class, 'store'])->name('admin.modules.store');
            Route::get('/{code}/configure', [ValuationModuleController::class, 'configure'])->name('admin.modules.configure');
            Route::put('/{code}', [ValuationModuleController::class, 'update'])->name('admin.modules.update');
            Route::delete('/{code}', [ValuationModuleController::class, 'destroy'])->name('admin.modules.destroy');
        });
    });

    // HPM, ABM and Choice Experiment modules — same shape for all three.
    foreach ([
        'hpm' => [HpmController::class, 'hpmId'],
        'abm' => [AbmController::class, 'abmId'],
        'ce' => [CeController::class, 'ceId'],
        'rcm' => [RcmController::class, 'rcmId'],
        'adc' => [AdcController::class, 'adcId'],
        'btm' => [BtmController::class, 'btmId'],
    ] as $slug => [$controller, $param]) {
        Route::prefix("/admin/projects/{projectId}/modules/{$slug}")->group(function () use ($controller, $slug, $param) {
            Route::get('/', [$controller, 'index'])->name("admin.modules.{$slug}.index");

            Route::middleware(['role:admin,surveyor'])->group(function () use ($controller, $slug, $param) {
                Route::get('/create', [$controller, 'create'])->name("admin.modules.{$slug}.create");
                Route::post('/', [$controller, 'store'])->name("admin.modules.{$slug}.store");
                Route::get('/{'.$param.'}/edit', [$controller, 'edit'])->name("admin.modules.{$slug}.edit");
                Route::put('/{'.$param.'}', [$controller, 'update'])->name("admin.modules.{$slug}.update");
                Route::delete('/{'.$param.'}', [$controller, 'destroy'])->name("admin.modules.{$slug}.destroy");
            });
        });
    }

    // Direct Use Value module
    Route::prefix('/admin/projects/{projectId}/modules/duv')->group(function () {
        Route::get('/', [DuvController::class, 'index'])->name('admin.modules.duv.index');

        Route::middleware(['role:admin,surveyor'])->group(function () {
            Route::get('/create', [DuvController::class, 'create'])->name('admin.modules.duv.create');
            Route::post('/', [DuvController::class, 'store'])->name('admin.modules.duv.store');
            Route::get('/{duvId}/edit', [DuvController::class, 'edit'])->name('admin.modules.duv.edit');
            Route::put('/{duvId}', [DuvController::class, 'update'])->name('admin.modules.duv.update');
            Route::delete('/{duvId}', [DuvController::class, 'destroy'])->name('admin.modules.duv.destroy');
        });
    });

    // Ecosystem service modules (Tabel 1) — one set of routes for all six
    // services; {service} selects the schema. Viewable by all roles.
    Route::prefix('/admin/projects/{projectId}/modules/ecosystem/{service}')->group(function () {
        Route::get('/', [EcosystemServiceController::class, 'index'])->name('admin.modules.ecosystem.index');

        Route::middleware(['role:admin,surveyor'])->group(function () {
            Route::get('/create', [EcosystemServiceController::class, 'create'])->name('admin.modules.ecosystem.create');
            Route::post('/', [EcosystemServiceController::class, 'store'])->name('admin.modules.ecosystem.store');
            Route::get('/{recordId}/edit', [EcosystemServiceController::class, 'edit'])->name('admin.modules.ecosystem.edit');
            Route::put('/{recordId}', [EcosystemServiceController::class, 'update'])->name('admin.modules.ecosystem.update');
            Route::delete('/{recordId}', [EcosystemServiceController::class, 'destroy'])->name('admin.modules.ecosystem.destroy');
        });
    });

    // EOP Module — viewable by all roles, entered by admin + surveyor
    Route::prefix('/admin/projects/{projectId}/modules/eop')->group(function () {
        Route::get('/', [EopController::class, 'index'])->name('admin.modules.eop.index');

        Route::middleware(['role:admin,surveyor'])->group(function () {
            Route::get('/create', [EopController::class, 'create'])->name('admin.modules.eop.create');
            Route::post('/', [EopController::class, 'store'])->name('admin.modules.eop.store');
            Route::get('/{eopId}/edit', [EopController::class, 'edit'])->name('admin.modules.eop.edit');
            Route::put('/{eopId}', [EopController::class, 'update'])->name('admin.modules.eop.update');
            Route::delete('/{eopId}', [EopController::class, 'destroy'])->name('admin.modules.eop.destroy');
        });
    });

    // TCM demand-model analyses — registered before the TCM data routes so
    // "/tcm/analysis" matches as a literal rather than as a {tcmId}.
    Route::prefix('/admin/projects/{projectId}/modules/tcm/analysis')->group(function () {
        Route::get('/', [TcmAnalysisController::class, 'index'])->name('admin.modules.tcm.analysis.index');

        Route::middleware(['role:admin,analyst'])->group(function () {
            Route::get('/create', [TcmAnalysisController::class, 'create'])->name('admin.modules.tcm.analysis.create');
            Route::post('/', [TcmAnalysisController::class, 'store'])->name('admin.modules.tcm.analysis.store');
            Route::post('/estimate', [TcmAnalysisController::class, 'estimate'])->name('admin.modules.tcm.analysis.estimate');
            Route::get('/{analysisId}/edit', [TcmAnalysisController::class, 'edit'])->name('admin.modules.tcm.analysis.edit');
            Route::put('/{analysisId}', [TcmAnalysisController::class, 'update'])->name('admin.modules.tcm.analysis.update');
            Route::delete('/{analysisId}', [TcmAnalysisController::class, 'destroy'])->name('admin.modules.tcm.analysis.destroy');
        });
    });

    // TCM Module — viewable by all roles, entered by admin + surveyor
    Route::prefix('/admin/projects/{projectId}/modules/tcm')->group(function () {
        Route::get('/', [TcmController::class, 'index'])->name('admin.modules.tcm.index');

        Route::middleware(['role:admin,surveyor'])->group(function () {
            Route::get('/create', [TcmController::class, 'create'])->name('admin.modules.tcm.create');
            Route::post('/', [TcmController::class, 'store'])->name('admin.modules.tcm.store');
            Route::get('/export', [TcmController::class, 'export'])->name('admin.modules.tcm.export');
            Route::post('/import', [TcmController::class, 'import'])->name('admin.modules.tcm.import');
            Route::get('/{tcmId}/edit', [TcmController::class, 'edit'])->name('admin.modules.tcm.edit');
            Route::put('/{tcmId}', [TcmController::class, 'update'])->name('admin.modules.tcm.update');
            Route::delete('/{tcmId}', [TcmController::class, 'destroy'])->name('admin.modules.tcm.destroy');
        });
    });

    // CVM logit/probit analyses — before the CVM data routes so "/cvm/analysis"
    // matches as a literal rather than as a {cvmId}.
    Route::prefix('/admin/projects/{projectId}/modules/cvm/analysis')->group(function () {
        Route::get('/', [CvmAnalysisController::class, 'index'])->name('admin.modules.cvm.analysis.index');

        Route::middleware(['role:admin,analyst'])->group(function () {
            Route::get('/create', [CvmAnalysisController::class, 'create'])->name('admin.modules.cvm.analysis.create');
            Route::post('/', [CvmAnalysisController::class, 'store'])->name('admin.modules.cvm.analysis.store');
            Route::post('/estimate', [CvmAnalysisController::class, 'estimate'])->name('admin.modules.cvm.analysis.estimate');
            Route::get('/{analysisId}/edit', [CvmAnalysisController::class, 'edit'])->name('admin.modules.cvm.analysis.edit');
            Route::put('/{analysisId}', [CvmAnalysisController::class, 'update'])->name('admin.modules.cvm.analysis.update');
            Route::delete('/{analysisId}', [CvmAnalysisController::class, 'destroy'])->name('admin.modules.cvm.analysis.destroy');
        });
    });

    // CVM Module — viewable by all roles, entered by admin + surveyor
    Route::prefix('/admin/projects/{projectId}/modules/cvm')->group(function () {
        Route::get('/', [CvmController::class, 'index'])->name('admin.modules.cvm.index');

        Route::middleware(['role:admin,surveyor'])->group(function () {
            Route::get('/create', [CvmController::class, 'create'])->name('admin.modules.cvm.create');
            Route::post('/', [CvmController::class, 'store'])->name('admin.modules.cvm.store');
            Route::get('/export', [CvmController::class, 'export'])->name('admin.modules.cvm.export');
            Route::post('/import', [CvmController::class, 'import'])->name('admin.modules.cvm.import');
            Route::get('/{cvmId}/edit', [CvmController::class, 'edit'])->name('admin.modules.cvm.edit');
            Route::put('/{cvmId}', [CvmController::class, 'update'])->name('admin.modules.cvm.update');
            Route::delete('/{cvmId}', [CvmController::class, 'destroy'])->name('admin.modules.cvm.destroy');
        });
    });

    // User Management — admin only
    Route::middleware(['role:admin'])->prefix('/admin/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    // Audit Log — admin only
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit.index');
    });

    // Master Data — admin only
    Route::middleware(['role:admin'])->prefix('/admin/master-data')->group(function () {
        // Market Prices
        Route::get('/prices', [MarketPriceController::class, 'index'])->name('admin.master.prices.index');
        Route::get('/prices/create', [MarketPriceController::class, 'create'])->name('admin.master.prices.create');
        Route::post('/prices', [MarketPriceController::class, 'store'])->name('admin.master.prices.store');
        Route::get('/prices/{id}/edit', [MarketPriceController::class, 'edit'])->name('admin.master.prices.edit');
        Route::put('/prices/{id}', [MarketPriceController::class, 'update'])->name('admin.master.prices.update');
        Route::delete('/prices/{id}', [MarketPriceController::class, 'destroy'])->name('admin.master.prices.destroy');

        // Environmental Coefficients
        Route::get('/coefficients', [EnvironmentalCoefficientController::class, 'index'])->name('admin.master.coefficients.index');
        Route::get('/coefficients/create', [EnvironmentalCoefficientController::class, 'create'])->name('admin.master.coefficients.create');
        Route::post('/coefficients', [EnvironmentalCoefficientController::class, 'store'])->name('admin.master.coefficients.store');
        Route::get('/coefficients/{id}/edit', [EnvironmentalCoefficientController::class, 'edit'])->name('admin.master.coefficients.edit');
        Route::put('/coefficients/{id}', [EnvironmentalCoefficientController::class, 'update'])->name('admin.master.coefficients.update');
        Route::delete('/coefficients/{id}', [EnvironmentalCoefficientController::class, 'destroy'])->name('admin.master.coefficients.destroy');
    });

    // Sensitivity Analysis — admin + analyst only
    Route::middleware(['role:admin,analyst'])->group(function () {
        Route::get('/admin/sensitivity', [SensitivityController::class, 'index'])->name('admin.sensitivity.index');
        Route::get('/admin/sensitivity/simulate', [SensitivityController::class, 'simulate'])->name('admin.sensitivity.simulate');
    });
});
