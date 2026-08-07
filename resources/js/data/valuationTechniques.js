// Table 2 of "Bahan Sistem Informasi VALEK" — the catalog of economic
// valuation techniques, grouped by approach. Mirrors App\Support\ValuationTechniques.
export const VALUATION_TECHNIQUES = [
    {
        approach: 'Harga Pasar',
        techniques: [
            { code: 'EOP', name: 'Change-in-productivity Approach / Effect of Production' },
            { code: 'HC', name: 'Loss-of-Earnings / Human Capital Approach' },
            { code: 'OC', name: 'Opportunity Cost Approach' },
        ],
    },
    {
        approach: 'Nilai Pengeluaran Langsung',
        techniques: [
            { code: 'CEA', name: 'Cost Effectiveness Analysis' },
            { code: 'PE', name: 'Preventive-Expenditure' },
            { code: 'CP', name: 'Compensation Payments' },
        ],
    },
    {
        approach: 'Nilai Pasar Implisit (Surrogate Market)',
        techniques: [
            { code: 'PV', name: 'Hedonic Value / Property-value Approach' },
            { code: 'WD', name: 'Wage-differential Approach' },
            { code: 'TCM', name: 'Travel-cost Method' },
            { code: 'ES', name: 'Marketed Goods as Environmental Surrogates' },
        ],
    },
    {
        approach: 'Nilai Pengeluaran Implisit',
        techniques: [
            { code: 'Rep.C', name: 'Replacement Cost' },
            { code: 'Rel.C', name: 'Relocation Cost' },
            { code: 'SPC', name: 'Shadow-Project Cost' },
        ],
    },
    {
        approach: 'Artificial Market',
        techniques: [{ code: 'CVM', name: 'Contingent Valuation Method' }],
    },
    {
        approach: 'Non-WTP',
        techniques: [{ code: 'EA', name: 'Energy Theory of Value - Energy Analysis' }],
    },
];

export const VALUATION_TECHNIQUE_CODES = VALUATION_TECHNIQUES.flatMap((g) => g.techniques.map((t) => t.code));
