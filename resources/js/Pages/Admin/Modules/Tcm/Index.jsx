import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import ImportExportBar from '../../../../Components/ui/ImportExportBar';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ project, tcmData, stats }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    return (
        <AdminLayout title={`Data TCM — ${project.name}`}>
            <Head title={`Data TCM — ${project.name}`} />

            <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali ke Proyek</Link>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                <div>
                    <h2 style={{ fontSize: 18, fontWeight: 700 }}>Travel Cost Method (TCM)</h2>
                    <p style={{ fontSize: 13, color: 'var(--text-muted)' }}>Data biaya perjalanan pengunjung</p>
                </div>
                {canManage && (
                    <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
                        <ImportExportBar
                            exportHref={route('admin.modules.tcm.export', project.id)}
                            importHref={route('admin.modules.tcm.import', project.id)}
                        />
                        <Link href={route('admin.modules.tcm.create', project.id)} className="btn btn-sm btn-primary">+ Tambah Data</Link>
                    </div>
                )}
            </div>

            {stats && (
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 12, marginBottom: 20 }} className="stagger">
                    <div className="stat-card"><div className="stat-label">Responden</div><div className="stat-value" style={{ fontSize: 20 }}>{stats.total_respondents}</div></div>
                    <div className="stat-card"><div className="stat-label">Rata-rata Jarak</div><div className="stat-value" style={{ fontSize: 20 }}>{formatRupiah(stats.avg_distance, 1)} km</div></div>
                    <div className="stat-card"><div className="stat-label">Rata-rata Surplus</div><div className="stat-value" style={{ fontSize: 20, color: 'var(--primary)' }}>Rp{formatRupiah(stats.avg_surplus)}</div></div>
                    <div className="stat-card"><div className="stat-label">Total Surplus</div><div className="stat-value" style={{ fontSize: 20, color: 'var(--success)' }}>Rp{formatRupiah(stats.total_surplus)}</div></div>
                </div>
            )}

            <div className="card">
                {tcmData.data.length ? (
                    <>
                        <div className="table-wrapper">
                            <table className="data-table">
                                <thead><tr><th>ID Responden</th><th>Asal</th><th>Jarak</th><th>Biaya Transport</th><th>Biaya Waktu</th><th>Frekuensi</th><th style={{ textAlign: 'right' }}>Surplus</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {tcmData.data.map((d) => (
                                        <tr key={d.id}>
                                            <td style={{ fontFamily: 'monospace', fontSize: 13 }}>{d.respondent_id}</td>
                                            <td style={{ fontSize: 13 }}>{d.origin_location || '-'}</td>
                                            <td>{formatRupiah(d.distance, 1)} km</td>
                                            <td>Rp{formatRupiah(d.transportation_cost)}</td>
                                            <td>Rp{formatRupiah(d.time_cost)}</td>
                                            <td style={{ textAlign: 'center' }}>{d.visit_frequency}x</td>
                                            <td style={{ textAlign: 'right', fontWeight: 700, color: 'var(--primary)' }}>Rp{formatRupiah(d.consumer_surplus)}</td>
                                            <td>
                                                {canManage && (
                                                    <div style={{ display: 'flex', gap: 4 }}>
                                                        <Link href={route('admin.modules.tcm.edit', [project.id, d.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                        <ConfirmDeleteButton href={route('admin.modules.tcm.destroy', [project.id, d.id])} />
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination paginator={tcmData} />
                    </>
                ) : (
                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>
                        Belum ada data TCM.{' '}
                        {canManage && <Link href={route('admin.modules.tcm.create', project.id)}>Tambah data pertama →</Link>}
                    </p>
                )}
            </div>
        </AdminLayout>
    );
}
