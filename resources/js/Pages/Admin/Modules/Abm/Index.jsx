import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ project, records, totals }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    return (
        <ModuleIndexShell
            title="Data ABM / Defensive Expenditure"
            projectName={project.name}
            backHref={route('admin.modules.index', project.id)}
            backLabel="Kembali ke Modul Valuasi"
            createHref={route('admin.modules.abm.create', project.id)}
            createLabel="Tambah Data ABM"
            canManage={canManage}
            cardTitle="Data Rumah Tangga"
            cardSubtitle="Total avoidance = biaya defensif + biaya medis + pendapatan hilang"
            isEmpty={!records.data.length}
            emptyText="Belum ada data ABM."
            stats={[
                { label: 'Jumlah Data', value: totals.records, unit: 'rumah tangga', color: 'var(--primary)' },
                { label: 'Pengeluaran Defensif', value: totals.defensive, format: 'currency', unit: 'Σ (Q×P) + waktu', color: 'var(--info)' },
                { label: 'Pendapatan Hilang', value: totals.lost_income, format: 'currency', unit: 'Σ hari sakit × upah', color: 'var(--warning)' },
                { label: 'Total Avoidance', value: totals.total_avoidance, format: 'currency', unit: 'nilai kerugian dihindari', color: 'var(--success)' },
            ]}
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Responden</th>
                            <th>Lokasi</th>
                            <th>Risiko</th>
                            <th>Tindakan Defensif</th>
                            <th style={{ textAlign: 'right' }}>Defensif</th>
                            <th style={{ textAlign: 'right' }}>Medis</th>
                            <th style={{ textAlign: 'right' }}>Pend. Hilang</th>
                            <th style={{ textAlign: 'right' }}>Total</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.respondent_code}</td>
                                <td style={{ fontSize: 13 }}>{r.location}</td>
                                <td style={{ fontSize: 12 }}><span className="badge badge-warning">{r.risk_type}</span></td>
                                <td style={{ fontSize: 13 }}>{r.defensive_action}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.defensive_expenditure, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.medical_cost, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.lost_income, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: 'var(--success)' }}>Rp{formatRupiah(r.total_avoidance, 0)}</td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.modules.abm.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.modules.abm.destroy', [project.id, r.id])} message={`Hapus data ${r.respondent_code}?`} />
                                        </div>
                                    </td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                    <tfoot>
                        <tr style={{ background: 'var(--surface-alt)' }}>
                            <td colSpan={4} style={{ fontWeight: 700, borderBottom: 'none' }}>TOTAL SELURUH DATA</td>
                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none' }}>Rp{formatRupiah(totals.defensive, 0)}</td>
                            <td style={{ borderBottom: 'none' }} />
                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none' }}>Rp{formatRupiah(totals.lost_income, 0)}</td>
                            <td style={{ textAlign: 'right', fontWeight: 700, borderBottom: 'none', color: 'var(--success)' }}>Rp{formatRupiah(totals.total_avoidance, 0)}</td>
                            {canManage && <td style={{ borderBottom: 'none' }} />}
                        </tr>
                    </tfoot>
                </table>
            </div>
            <Pagination paginator={records} />
        </ModuleIndexShell>
    );
}
