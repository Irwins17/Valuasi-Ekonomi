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
            title="Data Replacement Cost"
            projectName={project.name}
            backHref={route('admin.modules.index', project.id)}
            backLabel="Kembali ke Modul Valuasi"
            createHref={route('admin.modules.rcm.create', project.id)}
            createLabel="Tambah Data"
            canManage={canManage}
            cardTitle="Data Replacement Cost"
            cardSubtitle="Nilai total = Volume/Luas × Biaya Penggantian per Unit · Nilai tahunan = Nilai total ÷ Umur Manfaat"
            isEmpty={!records.data.length}
            emptyText="Belum ada data Replacement Cost."
            stats={[
                { label: 'Jumlah Data', value: totals.records, unit: 'record', color: 'var(--primary)' },
                { label: 'Nilai Total', value: totals.total, format: 'currency', color: 'var(--info)' },
                { label: 'Nilai Tahunan', value: totals.annual, format: 'currency', color: 'var(--success)' },
            ]}
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Data</th>
                            <th>Aset yang Digantikan</th>
                            <th>Lokasi</th>
                            <th style={{ textAlign: 'right' }}>Volume/Luas</th>
                            <th style={{ textAlign: 'right' }}>Biaya/Unit</th>
                            <th style={{ textAlign: 'right' }}>Umur (thn)</th>
                            <th style={{ textAlign: 'right' }}>Nilai Total</th>
                            <th style={{ textAlign: 'right' }}>Nilai Tahunan</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.record_code}</td>
                                <td style={{ fontSize: 13 }}>{r.asset_type}</td>
                                <td style={{ fontSize: 13 }}>{r.location}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>
                                    {formatRupiah(r.quantity, 2)}
                                    <span style={{ color: 'var(--text-muted)', fontSize: 11, marginLeft: 4 }}>{r.unit}</span>
                                </td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.replacement_cost_per_unit, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{r.useful_life_years ?? '—'}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.total_value, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: 'var(--success)' }}>Rp{formatRupiah(r.annual_value, 0)}</td>
                                <td><span className={`badge ${STATUS_BADGE[r.data_status] || 'badge-gray'}`}>{statuses[r.data_status]}</span></td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.modules.rcm.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.modules.rcm.destroy', [project.id, r.id])} message={`Hapus data ${r.record_code}?`} />
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
