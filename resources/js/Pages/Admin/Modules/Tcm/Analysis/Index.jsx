import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah, toNumber } from '../../../../../lib/format';

export default function Index({ project, analyses, respondentCount, models }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isAnalyst;

    return (
        <ModuleIndexShell
            title="Analisis TCM"
            projectName={project.name}
            backHref={route('admin.modules.tcm.index', project.id)}
            backLabel="Kembali ke Data Responden TCM"
            createHref={canManage ? route('admin.modules.tcm.analysis.create', project.id) : null}
            createLabel="Tambah Analisis"
            canManage={canManage}
            cardTitle="Model Permintaan Rekreasi"
            cardSubtitle="ln Vij = β₀ + β₁·TCij + γk·Xkij · CS = −1/β₁ · Nilai rekreasi = CS × Vtotal"
            isEmpty={!analyses.data.length}
            emptyText="Belum ada analisis TCM."
            stats={[
                { label: 'Responden Tersedia', value: respondentCount, unit: 'baris data TCM', color: 'var(--primary)' },
                { label: 'Jumlah Analisis', value: analyses.total, unit: 'model tersimpan', color: 'var(--info)' },
            ]}
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Analisis</th>
                            <th>Lokasi</th>
                            <th>Model</th>
                            <th style={{ textAlign: 'right' }}>β₁</th>
                            <th style={{ textAlign: 'right' }}>CS / Individu</th>
                            <th style={{ textAlign: 'right' }}>Pengunjung</th>
                            <th style={{ textAlign: 'right' }}>Nilai Rekreasi</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {analyses.data.map((a) => {
                            const slopeValid = toNumber(a.beta_1) < 0;
                            return (
                                <tr key={a.id}>
                                    <td style={{ fontSize: 13, fontWeight: 600 }}>{a.analysis_code}</td>
                                    <td style={{ fontSize: 13 }}>{a.site_name}</td>
                                    <td style={{ fontSize: 12 }}>
                                        <span className="badge badge-info">{models[a.regression_model]}</span>
                                        <div style={{ fontSize: 10.5, color: 'var(--text-muted)', marginTop: 3 }}>
                                            {a.coefficient_source === 'estimated' ? 'Estimasi data' : 'Input manual'}
                                        </div>
                                    </td>
                                    <td style={{ textAlign: 'right', fontSize: 12, fontFamily: 'ui-monospace, monospace' }}>{formatRupiah(a.beta_1, 8)}</td>
                                    <td style={{ textAlign: 'right', fontSize: 13 }}>
                                        {slopeValid ? `Rp${formatRupiah(a.consumer_surplus, 0)}` : <span style={{ color: 'var(--danger)' }}>—</span>}
                                    </td>
                                    <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(a.total_visitors, 0)}</td>
                                    <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: slopeValid ? 'var(--success)' : 'var(--text-muted)' }}>
                                        {slopeValid ? `Rp${formatRupiah(a.recreation_value, 0)}` : '—'}
                                    </td>
                                    <td>
                                        {!slopeValid ? (
                                            <span className="badge badge-danger" title="β₁ tidak negatif, surplus konsumen tidak bermakna">β₁ ≥ 0</span>
                                        ) : a.converged === false ? (
                                            <span className="badge badge-warning" title="Estimasi tidak konvergen">Tidak konvergen</span>
                                        ) : (
                                            <span className="badge badge-success">Valid</span>
                                        )}
                                    </td>
                                    {canManage && (
                                        <td>
                                            <div style={{ display: 'flex', gap: 4 }}>
                                                <Link href={route('admin.modules.tcm.analysis.edit', [project.id, a.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                <ConfirmDeleteButton href={route('admin.modules.tcm.analysis.destroy', [project.id, a.id])} message={`Hapus analisis ${a.analysis_code}?`} />
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
