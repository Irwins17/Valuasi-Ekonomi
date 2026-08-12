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
            title="Data Direct Use Value"
            projectName={project.name}
            backHref={route('admin.modules.index', project.id)}
            backLabel="Kembali ke Modul Valuasi"
            createHref={route('admin.modules.duv.create', project.id)}
            createLabel="Tambah Data DUV"
            canManage={canManage}
            cardTitle="Data Direct Use Value"
            cardSubtitle="DUV gross = Σ(Qi × Pi) · DUV net = Σ(Qi × Pi) − Ci"
            isEmpty={!records.data.length}
            emptyText="Belum ada data Direct Use Value."
            stats={[
                { label: 'Jumlah Data', value: totals.records, unit: 'record', color: 'var(--primary)' },
                { label: 'Gross DUV', value: totals.gross, format: 'currency', unit: 'Σ(Qi × Pi)', color: 'var(--info)' },
                { label: 'Total Biaya', value: totals.cost, format: 'currency', unit: 'Σ Ci', color: 'var(--warning)' },
                { label: 'Net DUV', value: totals.net, format: 'currency', unit: 'Gross − Biaya', color: totals.net >= 0 ? 'var(--success)' : 'var(--danger)' },
            ]}
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Data</th>
                            <th>Jenis Barang / Jasa</th>
                            <th>Lokasi</th>
                            <th style={{ textAlign: 'right' }}>Qi</th>
                            <th style={{ textAlign: 'right' }}>Pi</th>
                            <th style={{ textAlign: 'right' }}>Gross</th>
                            <th style={{ textAlign: 'right' }}>Biaya</th>
                            <th style={{ textAlign: 'right' }}>Net</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.record_code}</td>
                                <td style={{ fontSize: 13 }}>{r.goods_type}</td>
                                <td style={{ fontSize: 13 }}>{r.location}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>
                                    {formatRupiah(r.quantity, 2)}
                                    <span style={{ color: 'var(--text-muted)', fontSize: 11, marginLeft: 4 }}>{r.unit}</span>
                                </td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.market_price, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.gross_value, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13, color: 'var(--danger)' }}>Rp{formatRupiah(r.production_cost, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: 'var(--success)' }}>Rp{formatRupiah(r.net_value, 0)}</td>
                                <td><span className={`badge ${STATUS_BADGE[r.data_status] || 'badge-gray'}`}>{statuses[r.data_status]}</span></td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.modules.duv.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.modules.duv.destroy', [project.id, r.id])} message={`Hapus data ${r.record_code}?`} />
                                        </div>
                                    </td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                    <tfoot>
                        <tr style={{ background: 'var(--surface-alt)' }}>
                            <td colSpan={5} style={{ fontWeight: 700, borderBottom: 'none' }}>TOTAL (halaman ini &amp; seluruh data)</td>
                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none' }}>Rp{formatRupiah(totals.gross, 0)}</td>
                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none', color: 'var(--danger)' }}>Rp{formatRupiah(totals.cost, 0)}</td>
                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none', color: 'var(--success)' }}>Rp{formatRupiah(totals.net, 0)}</td>
                            <td style={{ borderBottom: 'none' }} colSpan={canManage ? 2 : 1} />
                        </tr>
                    </tfoot>
                </table>
            </div>
            <Pagination paginator={records} />
        </ModuleIndexShell>
    );
}
