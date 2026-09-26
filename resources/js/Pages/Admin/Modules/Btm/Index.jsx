import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

const STATUS_BADGE = { draft: 'badge-warning', verified: 'badge-info', final: 'badge-success' };

export default function Index({ project, records, totals, statuses }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    return (
        <ModuleIndexShell
            title="Data Benefit Transfer"
            projectName={project.name}
            backHref={route('admin.modules.index', project.id)}
            backLabel="Kembali ke Modul Valuasi"
            createHref={route('admin.modules.btm.create', project.id)}
            createLabel="Tambah Data"
            canManage={canManage}
            cardTitle="Data Benefit Transfer"
            cardSubtitle="Nilai transfer = Nilai Studi Sumber × Faktor Penyesuaian × Kuantitas Target"
            isEmpty={!records.data.length}
            emptyText="Belum ada data Benefit Transfer."
            stats={[
                { label: 'Jumlah Data', value: totals.records, unit: 'record', color: 'var(--primary)' },
                { label: 'Total Nilai Transfer', value: totals.transferred, format: 'currency', color: 'var(--success)' },
            ]}
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Data</th>
                            <th>Studi Sumber</th>
                            <th style={{ textAlign: 'right' }}>Nilai Sumber</th>
                            <th style={{ textAlign: 'right' }}>Faktor Penyesuaian</th>
                            <th style={{ textAlign: 'right' }}>Kuantitas Target</th>
                            <th style={{ textAlign: 'right' }}>Nilai Transfer</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.record_code}</td>
                                <td style={{ fontSize: 13 }}>
                                    {r.source_study_title}
                                    {r.source_study_year && <span style={{ color: 'var(--text-muted)' }}> ({r.source_study_year})</span>}
                                </td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.source_value, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(r.adjustment_factor, 2)}×</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(r.target_quantity, 2)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: 'var(--success)' }}>Rp{formatRupiah(r.transferred_value, 0)}</td>
                                <td><span className={`badge ${STATUS_BADGE[r.data_status] || 'badge-gray'}`}>{statuses[r.data_status]}</span></td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.modules.btm.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.modules.btm.destroy', [project.id, r.id])} message={`Hapus data ${r.record_code}?`} />
                                        </div>
                                    </td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination paginator={records} />
        </ModuleIndexShell>
    );
}
