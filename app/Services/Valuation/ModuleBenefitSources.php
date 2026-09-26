<?php

namespace App\Services\Valuation;

use App\Models\AbmData;
use App\Models\AdcData;
use App\Models\BtmData;
use App\Models\CvmAnalysis;
use App\Models\DuvData;
use App\Models\EcosystemServiceRecord;
use App\Models\EopData;
use App\Models\Project;
use App\Models\RcmData;
use App\Models\TcmAnalysis;
use App\Support\EcosystemServiceSchemas;

/**
 * The module results a project can turn into a Benefit.
 *
 * Everything a user could otherwise retype by hand is offered here instead:
 * the description, the amount, the year and the ecosystem-service group. That
 * is not just convenience — a benefit created this way keeps a reference to
 * the record it came from, so the figure can be traced and re-checked later
 * rather than becoming an anonymous number in a table.
 *
 * Which amount an EOP record contributes depends on the project's
 * `eop_value_basis`: net of production cost by default, gross when the study
 * deliberately values output before costs.
 */
class ModuleBenefitSources
{
    /** Modules that can supply a benefit, in the order they are offered. */
    public const MODULES = [
        'eop' => 'EOP — Effect on Production',
        'duv' => 'DUV — Nilai Pasar (Market Price)',
        'tcm' => 'TCM — Analisis Nilai Rekreasi',
        'cvm' => 'CVM — Analisis WTP',
        'abm' => 'ABM — Defensive Expenditure',
        'rcm' => 'RCM — Replacement Cost',
        'adc' => 'ADC — Avoided Damage Cost',
        'btm' => 'BTM — Benefit Transfer',
        'ecosystem_service' => 'Jasa Ekosistem (Tabel 1)',
        'manual' => 'Input manual (tanpa modul)',
    ];

    /**
     * @return array<string, array<int, array<string, mixed>>> keyed by module
     */
    public function forProject(Project $project): array
    {
        return [
            'eop' => $this->eop($project),
            'duv' => $this->duv($project),
            'tcm' => $this->tcm($project),
            'cvm' => $this->cvm($project),
            'abm' => $this->abm($project),
            'rcm' => $this->rcm($project),
            'adc' => $this->adc($project),
            'btm' => $this->btm($project),
            'ecosystem_service' => $this->ecosystemService($project),
        ];
    }

    /**
     * One specific module record, or null when it does not belong to the
     * project — which is what stops a benefit from being pointed at another
     * project's data.
     */
    public function find(Project $project, string $module, int $recordId): ?array
    {
        foreach ($this->forProject($project)[$module] ?? [] as $option) {
            if ((int) $option['id'] === $recordId) {
                return $option;
            }
        }

        return null;
    }

    private function eop(Project $project): array
    {
        $useNet = $project->valuation_settings->eop_value_basis !== 'gross';

        return EopData::where('project_id', $project->id)
            ->where('impact_type', 'positive')
            ->get()
            ->map(fn (EopData $row) => [
                'id' => $row->id,
                'label' => "{$row->commodity_name} — EOP",
                'description' => 'Produksi '.$row->commodity_name,
                'value' => (float) ($useNet ? $row->net_value : $row->total_value),
                'annual_value' => (float) ($useNet ? $row->net_value : $row->total_value),
                'period_year' => $row->period_year,
                'unit' => $row->unit,
                'ecosystem_service_group' => $row->service_category,
                'category' => 'direct_use',
                'subcategory' => 'production',
                'method_used' => 'EOP',
                'basis' => $useNet ? 'net' : 'gross',
                'detail' => sprintf(
                    'ΔQ = %s %s, %s value',
                    number_format((float) $row->production_change, 2, ',', '.'),
                    $row->unit,
                    $useNet ? 'net' : 'gross',
                ),
            ])
            ->values()
            ->all();
    }

    private function duv(Project $project): array
    {
        return DuvData::where('project_id', $project->id)
            ->get()
            ->map(fn (DuvData $row) => [
                'id' => $row->id,
                'label' => "{$row->record_code} — {$row->goods_type}",
                'description' => $row->goods_type.' — '.$row->location,
                'value' => (float) $row->net_value,
                'annual_value' => (float) $row->net_value,
                'period_year' => $row->period_year,
                'unit' => $row->unit,
                'ecosystem_service_group' => $row->service_category,
                'category' => 'direct_use',
                'subcategory' => 'production',
                'method_used' => 'DUV',
                'detail' => 'Net DUV = (Q × P) − C',
            ])
            ->values()
            ->all();
    }

    private function tcm(Project $project): array
    {
        return TcmAnalysis::where('project_id', $project->id)
            ->get()
            // A model whose slope is not negative has no meaningful surplus,
            // so it is not offered as a benefit at all.
            ->filter(fn (TcmAnalysis $row) => $row->surplusIsValid())
            ->map(fn (TcmAnalysis $row) => [
                'id' => $row->id,
                'label' => "{$row->analysis_code} — {$row->site_name}",
                'description' => 'Nilai rekreasi — '.$row->site_name,
                'value' => (float) $row->recreation_value,
                'annual_value' => (float) $row->recreation_value,
                'period_year' => $row->period_year,
                'unit' => 'Rp/tahun',
                'ecosystem_service_group' => 'cultural',
                'category' => 'direct_use',
                'subcategory' => 'recreation',
                'method_used' => 'TCM',
                'detail' => 'CS × total pengunjung',
            ])
            ->values()
            ->all();
    }

    private function cvm(Project $project): array
    {
        return CvmAnalysis::where('project_id', $project->id)
            ->get()
            ->filter(fn (CvmAnalysis $row) => $row->wtpIsValid())
            ->map(fn (CvmAnalysis $row) => [
                'id' => $row->id,
                'label' => "{$row->analysis_code} — {$row->scenario}",
                'description' => 'WTP masyarakat — '.$row->scenario,
                'value' => (float) $row->total_wtp,
                'annual_value' => (float) $row->total_wtp,
                'period_year' => $row->period_year,
                'unit' => 'Rp/tahun',
                'ecosystem_service_group' => 'cultural',
                'category' => 'existence_value',
                'subcategory' => 'existence_value',
                'method_used' => 'CVM',
                'detail' => 'Mean WTP × populasi',
            ])
            ->values()
            ->all();
    }

    private function abm(Project $project): array
    {
        return AbmData::where('project_id', $project->id)
            ->get()
            ->map(fn (AbmData $row) => [
                'id' => $row->id,
                'label' => "{$row->respondent_code} — {$row->risk_type}",
                'description' => 'Kerugian dihindari — '.$row->risk_type,
                'value' => (float) $row->total_avoidance,
                'annual_value' => (float) $row->total_avoidance,
                'period_year' => null,
                'unit' => 'Rp/tahun',
                'ecosystem_service_group' => 'regulating',
                'category' => 'indirect_use',
                'subcategory' => 'water_regulation',
                'method_used' => 'ABM',
                'detail' => 'Biaya defensif + medis + pendapatan hilang',
            ])
            ->values()
            ->all();
    }

    private function rcm(Project $project): array
    {
        return RcmData::where('project_id', $project->id)
            ->get()
            ->map(fn (RcmData $row) => [
                'id' => $row->id,
                'label' => "{$row->record_code} — {$row->asset_type}",
                'description' => 'Replacement cost — '.$row->asset_type,
                'value' => (float) $row->annual_value,
                'annual_value' => (float) $row->annual_value,
                'period_year' => $row->period_year,
                'unit' => $row->unit,
                'ecosystem_service_group' => $row->service_category,
                'category' => 'indirect_use',
                'subcategory' => 'water_regulation',
                'method_used' => 'RCM',
                'detail' => 'Nilai total ÷ umur manfaat',
            ])
            ->values()
            ->all();
    }

    private function adc(Project $project): array
    {
        return AdcData::where('project_id', $project->id)
            ->get()
            ->map(fn (AdcData $row) => [
                'id' => $row->id,
                'label' => "{$row->record_code} — {$row->damage_type}",
                'description' => 'Kerusakan dihindari — '.$row->damage_type,
                'value' => (float) $row->avoided_cost,
                'annual_value' => (float) $row->avoided_cost,
                'period_year' => $row->period_year,
                'unit' => 'Rp/tahun',
                'ecosystem_service_group' => $row->service_category,
                'category' => 'indirect_use',
                'subcategory' => 'water_regulation',
                'method_used' => 'ADC',
                'detail' => 'Luas terlindungi × biaya kerusakan × probabilitas',
            ])
            ->values()
            ->all();
    }

    private function btm(Project $project): array
    {
        return BtmData::where('project_id', $project->id)
            ->get()
            ->map(fn (BtmData $row) => [
                'id' => $row->id,
                'label' => "{$row->record_code} — {$row->source_study_title}",
                'description' => 'Transfer nilai — '.$row->source_study_title,
                'value' => (float) $row->transferred_value,
                'annual_value' => (float) $row->transferred_value,
                'period_year' => $row->period_year,
                'unit' => null,
                'ecosystem_service_group' => $row->service_category,
                'category' => 'indirect_use',
                'subcategory' => 'production',
                'method_used' => 'BTM',
                'detail' => 'Nilai studi sumber × faktor penyesuaian × kuantitas target',
            ])
            ->values()
            ->all();
    }

    private function ecosystemService(Project $project): array
    {
        return EcosystemServiceRecord::where('project_id', $project->id)
            ->get()
            ->map(function (EcosystemServiceRecord $row) {
                $schema = EcosystemServiceSchemas::find($row->service_key);

                return [
                    'id' => $row->id,
                    'label' => "{$row->record_code} — ".($schema['name'] ?? $row->service_key),
                    'description' => ($schema['name'] ?? $row->service_key).' — '.$row->location,
                    'value' => (float) $row->total_value,
                    'annual_value' => (float) $row->total_value,
                    'period_year' => $row->period_year,
                    'unit' => $schema['outputs'][0]['unit'] ?? null,
                    'ecosystem_service_group' => $row->service_category,
                    'service_key' => $row->service_key,
                    'category' => $row->service_category === 'provisioning' ? 'direct_use' : 'indirect_use',
                    'subcategory' => $this->ecosystemSubcategory($row->service_key),
                    'method_used' => $schema['formula'] ?? null,
                    'detail' => $schema['formula'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    /** Maps a Tabel 1 service onto the benefits table's fixed subcategory enum. */
    private function ecosystemSubcategory(string $serviceKey): string
    {
        return match ($serviceKey) {
            'FOOD', 'RAWMAT', 'GENRES' => 'production',
            'CLIMATE' => 'carbon_sequestration',
            'WATER', 'EROSION', 'HABITAT' => 'water_regulation',
            default => 'production',
        };
    }
}
