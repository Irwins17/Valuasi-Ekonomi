import { useEffect, useState } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import ModuleFormShell from '../../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../../Components/modules/OutputPreview';
import { formatRupiah, toNumber } from '../../../../../lib/format';

export default function Form({ project, analysis, models, covariateOptions, respondentCount, minRespondents }) {
    const isEdit = Boolean(analysis);
    const indexUrl = route('admin.modules.tcm.analysis.index', project.id);
    const { flash } = usePage().props;

    const [covariates, setCovariates] = useState(analysis?.diagnostics?.variables?.filter((v) => v !== 'travel_cost') || []);
    const [estimating, setEstimating] = useState(false);

    const { data, setData, post, put, processing, errors } = useForm({
        analysis_code: analysis?.analysis_code || '',
        site_name: analysis?.site_name || '',
        regression_model: analysis?.regression_model || 'poisson',
        coefficient_source: analysis?.coefficient_source || 'estimated',
        respondent_count: analysis?.respondent_count ?? respondentCount,
        total_visitors: analysis?.total_visitors ?? '',
        mean_travel_cost: analysis?.mean_travel_cost ?? '',
        beta_0: analysis?.beta_0 ?? '',
        beta_1: analysis?.beta_1 ?? '',
        coef_income: analysis?.coef_income ?? '',
        coef_age: analysis?.coef_age ?? '',
        coef_education: analysis?.coef_education ?? '',
        coef_substitute: analysis?.coef_substitute ?? '',
        estimation_method: analysis?.estimation_method || '',
        converged: analysis?.converged ?? null,
        log_likelihood: analysis?.log_likelihood ?? '',
        dispersion_alpha: analysis?.dispersion_alpha ?? '',
        diagnostics: analysis?.diagnostics || null,
        period_year: analysis?.period_year ?? '',
        data_source: analysis?.data_source || '',
        notes: analysis?.notes || '',
    });

    // Fold a fresh fit back into the form. The analyst still reviews and saves.
    useEffect(() => {
        const est = flash?.estimation;
        if (!est?.ok) return;

        setData((current) => ({
            ...current,
            coefficient_source: 'estimated',
            respondent_count: est.respondent_count,
            mean_travel_cost: est.mean_travel_cost,
            beta_0: est.coefficients.beta_0 ?? '',
            beta_1: est.coefficients.beta_1 ?? '',
            coef_income: est.coefficients.coef_income ?? '',
            coef_age: est.coefficients.coef_age ?? '',
            coef_education: est.coefficients.coef_education ?? '',
            coef_substitute: est.coefficients.coef_substitute ?? '',
            estimation_method: `IRLS ${est.diagnostics?.model || ''}`.trim(),
            converged: est.converged,
            log_likelihood: est.log_likelihood ?? '',
            dispersion_alpha: est.dispersion_alpha ?? '',
            diagnostics: est.diagnostics,
        }));
    }, [flash?.estimation]);

    const beta1 = toNumber(data.beta_1);
    const slopeValid = beta1 < 0;
    const cs = slopeValid ? -1 / beta1 : 0;
    const recreationValue = cs * toNumber(data.total_visitors);
    const canEstimate = respondentCount >= minRespondents;

    function runEstimation() {
        setEstimating(true);
        router.post(
            route('admin.modules.tcm.analysis.estimate', project.id),
            { regression_model: data.regression_model, covariates },
            { preserveScroll: true, preserveState: true, onFinish: () => setEstimating(false) },
        );
    }

    function toggleCovariate(key) {
        setCovariates((prev) => (prev.includes(key) ? prev.filter((c) => c !== key) : [...prev, key]));
    }

    const fields = [
        { name: 'analysis_code', label: 'ID Analisis', type: 'text', required: true, placeholder: 'Contoh: TCM-AN-01' },
        { name: 'site_name', label: 'Lokasi Wisata', type: 'text', required: true, placeholder: 'Contoh: Pantai Prigi' },
        { name: 'regression_model', label: 'Model Regresi', type: 'select', required: true, options: Object.entries(models) },
        { name: 'coefficient_source', label: 'Sumber Koefisien', type: 'select', required: true, options: [['estimated', 'Diestimasi dari data responden'], ['manual', 'Input manual']] },
        { name: 'respondent_count', label: 'Jumlah Responden', type: 'number', required: true, step: '1', suffix: 'orang' },
        { name: 'total_visitors', label: 'Total Pengunjung per Tahun', type: 'number', required: true, step: '1', placeholder: 'Contoh: 120000', suffix: 'orang/tahun', hint: 'Vtotal — pengali nilai rekreasi.' },
        { name: 'mean_travel_cost', label: 'Rata-rata TCij', type: 'currency', required: true, prefix: 'Rp' },
        { name: 'beta_0', label: 'Konstanta β₀', type: 'number', required: true, allowNegative: true },
        { name: 'beta_1', label: 'Koefisien Biaya Perjalanan β₁', type: 'number', required: true, allowNegative: true, hint: 'Harus negatif agar surplus konsumen bermakna.' },
        { name: 'coef_income', label: 'Koefisien Pendapatan', type: 'number', allowNegative: true },
        { name: 'coef_age', label: 'Koefisien Usia', type: 'number', allowNegative: true },
        { name: 'coef_education', label: 'Koefisien Pendidikan', type: 'number', allowNegative: true },
        { name: 'coef_substitute', label: 'Koefisien Situs Pengganti', type: 'number', allowNegative: true },
        { name: 'estimation_method', label: 'Metode Estimasi', type: 'text', placeholder: 'Contoh: IRLS poisson' },
        { name: 'period_year', label: 'Periode / Tahun', type: 'year' },
        { name: 'data_source', label: 'Sumber Data', type: 'text', placeholder: 'Contoh: Survei pengunjung 2026' },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500 },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.tcm.analysis.update', [project.id, analysis.id]));
        else post(route('admin.modules.tcm.analysis.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Analisis TCM`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Analisis TCM"
            formTitle={`Form Analisis TCM — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <div className="card" style={{ marginBottom: 16 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12 }}>
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2"><path d="M3 3v18h18" /><path d="M19 9l-5 5-4-4-3 3" /></svg>
                            <h3 style={{ fontSize: 14, fontWeight: 700 }}>Estimasi dari Data Responden</h3>
                        </div>

                        <p style={{ fontSize: 11.5, color: 'var(--text-muted)', lineHeight: 1.55, marginBottom: 10 }}>
                            Menghitung β dari {respondentCount} responden TCM proyek ini dengan regresi {models[data.regression_model]}.
                        </p>

                        <div style={{ marginBottom: 10 }}>
                            <div style={{ fontSize: 11.5, fontWeight: 600, marginBottom: 6 }}>Variabel penjelas tambahan</div>
                            {Object.entries(covariateOptions).map(([key, label]) => (
                                <label key={key} style={{ display: 'flex', alignItems: 'center', gap: 7, fontSize: 12, marginBottom: 4, cursor: 'pointer' }}>
                                    <input type="checkbox" checked={covariates.includes(key)} onChange={() => toggleCovariate(key)} style={{ accentColor: 'var(--primary)' }} />
                                    {label}
                                </label>
                            ))}
                        </div>

                        <button type="button" className="btn btn-sm btn-secondary" style={{ width: '100%' }} onClick={runEstimation} disabled={!canEstimate || estimating}>
                            {estimating ? 'Menghitung…' : 'Jalankan Estimasi'}
                        </button>

                        {!canEstimate && (
                            <p style={{ fontSize: 11, color: 'var(--danger)', marginTop: 8, lineHeight: 1.5 }}>
                                Butuh minimal {minRespondents} responden; saat ini {respondentCount}.
                            </p>
                        )}

                        {data.converged === false && (
                            <p style={{ fontSize: 11, color: 'var(--danger)', marginTop: 8, lineHeight: 1.5 }}>
                                Estimasi terakhir tidak konvergen — koefisien belum layak dipakai.
                            </p>
                        )}

                        {data.diagnostics?.variables && (
                            <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 8, lineHeight: 1.55 }}>
                                Variabel: {data.diagnostics.variables.join(', ')}
                                {data.diagnostics.dropped_no_variation?.length > 0 && (
                                    <><br />Dilewati (tanpa variasi): {data.diagnostics.dropped_no_variation.join(', ')}</>
                                )}
                                {data.log_likelihood !== '' && <><br />Log-likelihood: {formatRupiah(data.log_likelihood, 3)}</>}
                            </div>
                        )}
                    </div>

                    <FormulaPanel
                        title="Formula TCM"
                        formulas={['ln Vij = β₀ + β₁·TCij + γk·Xkij', 'CS individu = −1 / β₁', 'Nilai rekreasi = CS × Vtotal']}
                        legend={[
                            { sym: 'Vij', desc: 'Jumlah kunjungan responden i dari zona j' },
                            { sym: 'TCij', desc: 'Biaya perjalanan responden' },
                            { sym: 'Xkij', desc: 'Karakteristik sosial-ekonomi' },
                        ]}
                    />

                    <OutputPreview
                        rows={[
                            { label: 'CS per Individu', sublabel: slopeValid ? '−1 / β₁' : 'β₁ harus negatif', value: slopeValid ? cs : 'Tidak berlaku', format: slopeValid ? 'currency' : 'raw', unit: slopeValid ? 'per kunjungan' : '', color: slopeValid ? '#6366f1' : '#ef4444' },
                            { label: 'Total Pengunjung', sublabel: 'Vtotal per tahun', value: toNumber(data.total_visitors), format: 'number', decimals: 0, unit: 'orang/tahun', color: '#f59e0b' },
                            { label: 'Nilai Rekreasi', sublabel: 'CS × Vtotal', value: slopeValid ? recreationValue : 'Tidak berlaku', format: slopeValid ? 'currency' : 'raw', unit: slopeValid ? 'per tahun' : '', color: slopeValid ? '#10b981' : '#ef4444' },
                        ]}
                        note={slopeValid
                            ? 'Surplus konsumen berlaku hanya bila β₁ negatif (kunjungan turun saat biaya naik).'
                            : 'β₁ tidak negatif, sehingga −1/β₁ tidak bermakna sebagai surplus konsumen dan nilai tidak dihitung.'}
                    />
                </>
            }
        />
    );
}
