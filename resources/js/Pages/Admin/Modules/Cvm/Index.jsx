import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import ImportExportBar from '../../../../Components/ui/ImportExportBar';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ project, cvmData, stats }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    return (
        <AdminLayout title={`Data CVM — ${project.name}`}>
            <Head title={`Data CVM — ${project.name}`} />

            <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali ke Proyek</Link>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                <div>
                    <h2 style={{ fontSize: 18, fontWeight: 700 }}>Contingent Valuation Method (CVM)</h2>
                    <p style={{ fontSize: 13, color: 'var(--text-muted)' }}>Data Willingness to Pay (WTP) responden</p>
                </div>
                {canManage && (
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        <ImportExportBar
                            exportHref={route('admin.modules.cvm.export', project.id)}
                            importHref={route('admin.modules.cvm.import', project.id)}
                        />
                        <Link href={route('admin.modules.cvm.create', project.id)} className="btn btn-sm btn-primary">+ Tambah Data</Link>
                    </div>
                )}
            </div>

            {stats && (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 12, marginBottom: 20 }} className="stagger">
                    <div className="stat-card"><div className="stat-label">Responden</div><div className="stat-value" style={{ fontSize: 20 }}>{stats.total_respondents}</div></div>
                    <div className="stat-card"><div className="stat-label">Bersedia Bayar</div><div className="stat-value" style={{ fontSize: 20, color: 'var(--success)' }}>{stats.willing_to_pay_count}</div></div>
                    <div className="stat-card"><div className="stat-label">Mean WTP</div><div className="stat-value" style={{ fontSize: 20, color: 'var(--primary)' }}>Rp{formatRupiah(stats.mean_wtp)}</div></div>
                    <div className="stat-card"><div className="stat-label">Median WTP</div><div className="stat-value" style={{ fontSize: 20, color: 'var(--accent)' }}>Rp{formatRupiah(stats.median_wtp)}</div></div>
                </div>
            )}

            <div className="card">
                {cvmData.data.length ? (
                    <>
                        <div className="table-wrapper">
                            <table className="data-table">
                                <thead><tr><th>ID Responden</th><th>WTP</th><th>Bersedia?</th><th>Pendapatan RT</th><th>Pendidikan</th><th>Lokasi</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {cvmData.data.map((d) => (
                                        <tr key={d.id}>
                                            <td style={{ fontFamily: 'monospace', fontSize: 13 }}>{d.respondent_id}</td>
                                            <td style={{ fontWeight: 700, color: 'var(--primary)' }}>Rp{formatRupiah(d.wtp)}</td>
                                            <td><span className={`badge ${d.willing_to_pay ? 'badge-success' : 'badge-danger'}`}>{d.willing_to_pay ? 'Ya' : 'Tidak'}</span></td>
                                            <td style={{ fontSize: 13 }}>{d.household_income ? `Rp${formatRupiah(d.household_income)}` : '-'}</td>
                                            <td style={{ fontSize: 13 }}>{d.education_level || '-'}</td>
                                            <td style={{ fontSize: 13 }}>{d.respondent_location || '-'}</td>
                                            <td>
                                                {canManage && (
                                                    <div style={{ display: 'flex', gap: 4 }}>
                                                        <Link href={route('admin.modules.cvm.edit', [project.id, d.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                        <ConfirmDeleteButton href={route('admin.modules.cvm.destroy', [project.id, d.id])} />
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination paginator={cvmData} />
                    </>
                ) : (
                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>
                        Belum ada data CVM.{' '}
                        {canManage && <Link href={route('admin.modules.cvm.create', project.id)}>Tambah data pertama →</Link>}
                    </p>
                )}
            </div>
        </AdminLayout>
    );
}
