import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ project, eopData }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    return (
        <AdminLayout title={`Data EOP — ${project.name}`}>
            <Head title={`Data EOP — ${project.name}`} />

            <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali ke Proyek</Link>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                <div>
                    <h2 style={{ fontSize: 18, fontWeight: 700 }}>Effect on Production (EOP)</h2>
                    <p style={{ fontSize: 13, color: 'var(--text-muted)' }}>Data perubahan volume produksi komoditas</p>
                </div>
                {canManage && <Link href={route('admin.modules.eop.create', project.id)} className="btn btn-sm btn-primary">+ Tambah Data</Link>}
            </div>

            <div className="card">
                {eopData.data.length ? (
                    <>
                        <div className="table-wrapper">
                            <table className="data-table">
                                <thead><tr><th>Komoditas</th><th>Produksi Sebelum</th><th>Produksi Sesudah</th><th>Δ Produksi</th><th>Harga Pasar</th><th style={{ textAlign: 'right' }}>Total Nilai</th><th>Dampak</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {eopData.data.map((d) => (
                                        <tr key={d.id}>
                                            <td style={{ fontWeight: 600 }}>{d.commodity_name}</td>
                                            <td>{formatRupiah(d.production_before)} {d.unit}</td>
                                            <td>{formatRupiah(d.production_after)} {d.unit}</td>
                                            <td style={{ fontWeight: 600, color: d.production_change >= 0 ? 'var(--success)' : 'var(--danger)' }}>
                                                {d.production_change >= 0 ? '+' : ''}{formatRupiah(d.production_change)}
                                            </td>
                                            <td>Rp{formatRupiah(d.market_price)}</td>
                                            <td style={{ textAlign: 'right', fontWeight: 700, color: 'var(--primary)' }}>Rp{formatRupiah(d.total_value)}</td>
                                            <td><span className={`badge ${d.impact_type === 'positive' ? 'badge-success' : 'badge-danger'}`}>{d.impact_type.replace(/^./, (c) => c.toUpperCase())}</span></td>
                                            <td>
                                                {canManage && (
                                                    <div style={{ display: 'flex', gap: 4 }}>
                                                        <Link href={route('admin.modules.eop.edit', [project.id, d.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                        <ConfirmDeleteButton href={route('admin.modules.eop.destroy', [project.id, d.id])} />
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination paginator={eopData} />
                    </>
                ) : (
                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>
                        Belum ada data EOP.{' '}
                        {canManage && <Link href={route('admin.modules.eop.create', project.id)}>Tambah data pertama →</Link>}
                    </p>
                )}
            </div>
        </AdminLayout>
    );
}
