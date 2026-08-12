import { useEffect, useState } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import ModuleFormShell from '../../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../../Components/modules/OutputPreview';
import { formatRupiah, toNumber } from '../../../../../lib/format';

function normalCdf(x) {
    // Abramowitz & Stegun 7.1.26, mirroring GlmSolver::normalCdf.
    const z = x / Math.SQRT2;
    const sign = z < 0 ? -1 : 1;
    const a = Math.abs(z);
    const t = 1 / (1 + 0.3275911 * a);
    const poly = ((((1.061405429 * t - 1.453152027) * t + 1.421413741) * t - 0.284496736) * t + 0.254829592) * t;
    return 0.5 * (1 + sign * (1 - poly * Math.exp(-a * a)));
}

export default function Form({ project, analysis, models, covariateOptions, dichotomousCount, minRespondents }) {
    const isEdit = Boolean(analysis);
    const indexUrl = route('admin.modules.cvm.analysis.index', project.id);
    const { flash } = usePage().props;

    const [covariates, setCovariates] = useState(analysis?.diagnostics?.variables?.filter((v) => v !== 'bid') || []);
    const [estimating, setEstimating] = useState(false);

    const { data, setData, post, put, processing, errors } = useForm({
        analysis_code: analysis?.analysis_code || '',
        scenario: analysis?.scenario || '',
        model: analysis?.model || 'logit',
        coefficient_source: analysis?.coefficient_source || 'estimated',
        question_type: analysis?.question_type || 'Dichotomous Choice',
        bid_value: analysis?.bid_value ?? '',
        respondent_count: analysis?.respondent_count ?? dichotomousCount,
        yes_count: analysis?.yes_count ?? '',
        no_count: analysis?.no_count ?? '',
        alpha: analysis?.alpha ?? '',
        beta_bid: analysis?.beta_bid ?? '',
        coef_income: analysis?.coef_income ?? '',
        coef_education: analysis?.coef_education ?? '',
        coef_age: analysis?.coef_age ?? '',
        mean_covariates: analysis?.mean_covariates || {},
        target_population: analysis?.target_population ?? '',
        converged: analysis?.converged ?? null,
        log_likelihood: analysis?.log_likelihood ?? '',
        diagnostics: analysis?.diagnostics || null,
        period_year: analysis?.period_year ?? '',
        data_source: analysis?.data_source || '',
        notes: analysis?.notes || '',
    });

    useEffect(() => {
        const est = flash?.estimation;
        if (!est?.ok) return;

        setData((current) => ({
            ...current,
            coefficient_source: 'estimated',
            respondent_count: est.respondent_count,
            yes_count: est.yes_count,
            no_count: est.no_count,
            bid_value: current.bid_value === '' ? est.mean_bid : current.bid_value,
            alpha: est.coefficients.alpha ?? '',
            beta_bid: est.coefficients.beta_bid ?? '',
            coef_income: est.coefficients.coef_income ?? '',
            coef_education: est.coefficients.coef_education ?? '',
            coef_age: est.coefficients.coef_age ?? '',
            mean_covariates: est.mean_covariates || {},
            converged: est.converged,
            log_likelihood: est.log_likelihood ?? '',
            diagnostics: est.diagnostics,
        }));
    }, [flash?.estimation]);

    const alpha = toNumber(data.alpha);
    const beta = toNumber(data.beta_bid);
    const covariateTerm = Object.values(data.mean_covariates || {}).reduce((s, v) => s + toNumber(v), 0);
    const wtpValid = Math.abs(beta) > 1e-12 && (alpha + covariateTerm) / beta > 0;
    const meanWtp = wtpValid ? (alpha + covariateTerm) / beta : 0;
    const totalWtp = meanWtp * toNumber(data.target_population);

    const linear = alpha + covariateTerm - beta * toNumber(data.bid_value);
    const pYes = data.model === 'probit'
        ? normalCdf(linear)
        : 1 / (1 + Math.exp(-Math.max(-30, Math.min(30, linear))));

    const canEstimate = dichotomousCount >= minRespondents;

    function runEstimation() {
        setEstimating(true);
        router.post(
            route('admin.modules.cvm.analysis.estimate', project.id),
            { model: data.model, covariates },
            { preserveScroll: true, preserveState: true, onFinish: () => setEstimating(false) },
        );
    }

    function toggleCovariate(key) {
        setCovariates((prev) => (prev.includes(key) ? prev.filter((c) => c !== key) : [...prev, key]));
    }

    const fields = [
        { name: 'analysis_code', label: 'ID Analisis', type: 'text', required: true, placeholder: 'Contoh: CVM-AN-01' },
        { name: 'scenario', label: 'Skenario Lingkungan / Program', type: 'text', required: true, placeholder: 'Contoh: Rehabilitasi mangrove 100 ha' },
        { name: 'model', label: 'Metode', type: 'select', required: true, options: Object.entries(models) },
        { name: 'coefficient_source', label: 'Sumber Koefisien', type: 'select', required: true, options: [['estimated', 'Diestimasi dari data responden'], ['manual', 'Input manual']] },
        { name: 'question_type', label: 'Jenis Pertanyaan', type: 'text', required: true },
        { name: 'bid_value', label: 'Bid Value Acuan (Ai)', type: 'currency', prefix: 'Rp', hint: 'Dipakai menghitung P(Ya) pada pratinjau.' },
        { name: 'respondent_count', label: 'Jumlah Responden', type: 'number', required: true, step: '1', suffix: 'orang' },
        { name: 'yes_count', label: 'Jumlah Ya', type: 'number', required: true, step: '1', suffix: 'orang' },
        { name: 'no_count', label: 'Jumlah Tidak', type: 'number', required: true, step: '1', suffix: 'orang' },
        { name: 'alpha', label: 'Konstanta α', type: 'number', required: true, allowNegative: true },
        { name: 'beta_bid', label: 'Koefisien β (Bid)', type: 'number', required: true, allowNegative: true, hint: 'Positif berarti tawaran lebih tinggi menurunkan kesediaan — sesuai P(Ya)=F(α − βA + γX).' },
        { name: 'coef_income', label: 'Koefisien Pendapatan', type: 'number', allowNegative: true },
        { name: 'coef_education', label: 'Koefisien Pendidikan', type: 'number', allowNegative: true },
        { name: 'coef_age', label: 'Koefisien Usia', type: 'number', allowNegative: true },
        { name: 'target_population', label: 'Populasi Sasaran', type: 'number', required: true, step: '1', placeholder: 'Contoh: 25000', suffix: 'orang/RT' },
        { name: 'period_year', label: 'Periode / Tahun', type: 'year' },
        { name: 'data_source', label: 'Sumber Data', type: 'text', placeholder: 'Contoh: Survei rumah tangga 2026' },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500 },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.cvm.analysis.update', [project.id, analysis.id]));
        else post(route('admin.modules.cvm.analysis.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Analisis CVM`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Analisis CVM"
            formTitle={`Form Analisis Logit / Probit — ${project.name}`}
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
                            {dichotomousCount} responden dichotomous choice tersedia pada proyek ini.
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
                                Butuh minimal {minRespondents} responden dichotomous choice bernilai tawaran; saat ini {dichotomousCount}.
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
                        title="Formula Logit / Probit"
                        formulas={[
                            'P(Ya) = 1 / [1 + exp(−(α − βAi + γkXki))]',
                            'Mean WTP = (α + γkX̄k) / β',
                            'Total WTP = Mean WTP × Npopulasi',
                        ]}
                        legend={[
                            { sym: 'Ai', desc: 'Nilai tawaran yang disodorkan' },
                            { sym: 'β', desc: 'Koefisien bid (positif = tawaran naik, kesediaan turun)' },
                            { sym: 'X̄k', desc: 'Rata-rata karakteristik responden' },
                        ]}
                    />

                    <OutputPreview
                        rows={[
                            { label: 'Probabilitas Ya', sublabel: `Pada bid Rp${formatRupiah(data.bid_value, 0)}`, value: pYes, format: 'decimal', decimals: 4, unit: 'peluang', color: '#6366f1' },
                            { label: 'Mean WTP', sublabel: wtpValid ? '(α + γX̄) / β' : 'Koefisien tidak menghasilkan WTP positif', value: wtpValid ? meanWtp : 'Tidak berlaku', format: wtpValid ? 'currency' : 'raw', unit: wtpValid ? 'per responden' : '', color: wtpValid ? '#f59e0b' : '#ef4444' },
                            { label: 'Total WTP', sublabel: 'Mean WTP × populasi', value: wtpValid ? totalWtp : 'Tidak berlaku', format: wtpValid ? 'currency' : 'raw', unit: wtpValid ? 'per tahun' : '', color: wtpValid ? '#10b981' : '#ef4444' },
                        ]}
                        note={wtpValid
                            ? 'Mean WTP memakai rata-rata karakteristik responden dari estimasi terakhir.'
                            : 'β nol atau tanda koefisien menghasilkan WTP negatif, sehingga nilai tidak dihitung.'}
                    />
                </>
            }
        />
    );
}
