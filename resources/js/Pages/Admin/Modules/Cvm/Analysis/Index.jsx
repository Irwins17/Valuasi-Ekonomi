import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah, toNumber } from '../../../../../lib/format';

export default function Index({ project, analyses, models, dichotomousCount }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isAnalyst;

    return (
        <ModuleIndexShell
            title="Analisis CVM (Logit / Probit)"
            projectName={project.name}
            backHref={route('admin.modules.cvm.index', project.id)}
            backLabel="Kembali ke Data Responden CVM"
            createHref={canManage ? route('admin.modules.cvm.analysis.create', project.id) : null}
            createLabel="Tambah Analisis"
            canManage={canManage}
            cardTitle="Model Dichotomous Choice"
            cardSubtitle="P(Ya) = F(α − βAi + γkXki) · Mean WTP = (α + γX̄)/β · Total WTP = Mean WTP × N"
            isEmpty={!analyses.data.length}
            emptyText="Belum ada analisis CVM."
            stats={[
                { label: 'Responden Dichotomous', value: dichotomousCount, unit: 'siap dianalisis', color: 'var(--primary)' },
                { label: 'Jumlah Analisis', value: analyses.total, unit: 'model tersimpan', color: 'var(--info)' },
            ]}
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Analisis</th>
                            <th>Skenario</th>
                            <th>Model</th>
                            <th style={{ textAlign: 'right' }}>α</th>
                            <th style={{ textAlign: 'right' }}>β</th>
                            <th style={{ textAlign: 'right' }}>P(Ya)</th>
                            <th style={{ textAlign: 'right' }}>Mean WTP</th>
                            <th style={{ textAlign: 'right' }}>Total WTP</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {analyses.data.map((a) => {
                            const valid = toNumber(a.mean_wtp) > 0;
                            return (
                                <tr key={a.id}>
                                    <td style={{ fontSize: 13, fontWeight: 600 }}>{a.analysis_code}</td>
                                    <td style={{ fontSize: 13 }}>{a.scenario}</td>
                                    <td style={{ fontSize: 12 }}>
                                        <span className="badge badge-purple">{models[a.model]}</span>
                                        <div style={{ fontSize: 10.5, color: 'var(--text-muted)', marginTop: 3 }}>
                                            {a.coefficient_source === 'estimated' ? 'Estimasi data' : 'Input manual'}
                                        </div>
                                    </td>
                                    <td style={{ textAlign: 'right', fontSize: 12, fontFamily: 'ui-monospace, monospace' }}>{formatRupiah(a.alpha, 5)}</td>
                                    <td style={{ textAlign: 'right', fontSize: 12, fontFamily: 'ui-monospace, monospace' }}>{formatRupiah(a.beta_bid, 8)}</td>
                                    <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(a.probability_yes, 4)}</td>
                                    <td style={{ textAlign: 'right', fontSize: 13 }}>
                                        {valid ? `Rp${formatRupiah(a.mean_wtp, 0)}` : <span style={{ color: 'var(--danger)' }}>—</span>}
                                    </td>
                                    <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: valid ? 'var(--success)' : 'var(--text-muted)' }}>
                                        {valid ? `Rp${formatRupiah(a.total_wtp, 0)}` : '—'}
                                    </td>
                                    <td>
                                        {!valid ? (
                                            <span className="badge badge-danger" title="Koefisien tidak menghasilkan WTP positif">WTP tidak valid</span>
                                        ) : a.converged === false ? (
                                            <span className="badge badge-warning">Tidak konvergen</span>
                                        ) : (
                                            <span className="badge badge-success">Valid</span>
                                        )}
                                    </td>
                                    {canManage && (
                                        <td>
                                            <div style={{ display: 'flex', gap: 4 }}>
                                                <Link href={route('admin.modules.cvm.analysis.edit', [project.id, a.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                <ConfirmDeleteButton href={route('admin.modules.cvm.analysis.destroy', [project.id, a.id])} message={`Hapus analisis ${a.analysis_code}?`} />
                                            </div>
                                        </td>
                                    )}
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
            <Pagination paginator={analyses} />
        </ModuleIndexShell>
    );
}
