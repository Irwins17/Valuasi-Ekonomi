<?php

namespace App\Support;

/**
 * Table 2 of "Bahan Sistem Informasi VALEK" — the catalog of economic
 * valuation techniques (Metode valuasi ekonomi yang digunakan untuk nilai
 * jasa ekosistem), grouped by approach. Mirrors
 * resources/js/data/valuationTechniques.js for the frontend.
 */
class ValuationTechniques
{
    /**
     * @var array<int, array{approach: string, techniques: array<int, array{code: string, name: string}>}>
     */
    public const CATALOG = [
        [
            'approach' => 'Harga Pasar',
            'techniques' => [
                ['code' => 'EOP', 'name' => 'Change-in-productivity Approach / Effect of Production'],
                ['code' => 'HC', 'name' => 'Loss-of-Earnings / Human Capital Approach'],
                ['code' => 'OC', 'name' => 'Opportunity Cost Approach'],
            ],
        ],
        [
            'approach' => 'Nilai Pengeluaran Langsung',
            'techniques' => [
                ['code' => 'CEA', 'name' => 'Cost Effectiveness Analysis'],
                ['code' => 'PE', 'name' => 'Preventive-Expenditure'],
                ['code' => 'CP', 'name' => 'Compensation Payments'],
            ],
        ],
        [
            'approach' => 'Nilai Pasar Implisit (Surrogate Market)',
            'techniques' => [
                ['code' => 'PV', 'name' => 'Hedonic Value / Property-value Approach'],
                ['code' => 'WD', 'name' => 'Wage-differential Approach'],
                ['code' => 'TCM', 'name' => 'Travel-cost Method'],
                ['code' => 'ES', 'name' => 'Marketed Goods as Environmental Surrogates'],
            ],
        ],
        [
            'approach' => 'Nilai Pengeluaran Implisit',
            'techniques' => [
                ['code' => 'Rep.C', 'name' => 'Replacement Cost'],
                ['code' => 'Rel.C', 'name' => 'Relocation Cost'],
                ['code' => 'SPC', 'name' => 'Shadow-Project Cost'],
            ],
        ],
        [
            'approach' => 'Artificial Market',
            'techniques' => [
                ['code' => 'CVM', 'name' => 'Contingent Valuation Method'],
            ],
        ],
        [
            'approach' => 'Non-WTP',
            'techniques' => [
                ['code' => 'EA', 'name' => 'Energy Theory of Value - Energy Analysis'],
            ],
        ],
    ];

    /**
     * Flat list of every technique code, in catalog order — e.g. for a
     * simple <select>, or Rule::in() validation on `method_used`.
     *
     * @return string[]
     */
    public static function codes(): array
    {
        return array_merge(...array_map(
            fn (array $group) => array_column($group['techniques'], 'code'),
            self::CATALOG
        ));
    }
}
