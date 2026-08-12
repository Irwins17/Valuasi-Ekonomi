import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ project, schema, records, totals, serviceCategories }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    return (
        <AdminLayout title={`Data ${schema.name}`} subtitle={<>Detail Proyek: <strong style={{ color: 'var(--primary)' }}>{project.name}</strong></>}>
            <Head title={`Data ${schema.name}`} />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20, gap: 12, flexWrap: 'wrap' }}>
                <Link href={route('admin.modules.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13 }}>
                    ← Kembali ke Modul Valuasi
                </Link>
                {canManage && (
                    <Link href={route('admin.modules.ecosystem.create', [project.id, schema.key])} className="btn btn-sm btn-primary">
                        + Tambah Data {schema.name}
                    </Link>
                )}
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 16, marginBottom: 20 }}>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">Jumlah Data</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--primary)' }}>{totals.records}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>record</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">Total Luas Area</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--info)' }}>{formatRupiah(totals.total_area, 2)}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>ha</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">Total Nilai Ekonomi</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--success)' }}>Rp{formatRupiah(totals.total_value, 0)}</div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>{schema.formula}</div>
                </div>
            </div>

            <div className="card">
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16, gap: 10, flexWrap: 'wrap' }}>
                    <div>
                        <h3 style={{ fontSize: 15, fontWeight: 700 }}>Data {schema.name}</h3>
                        <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>
                            <span className="badge badge-info" style={{ marginRight: 6 }}>{serviceCategories[schema.service_category]}</span>
                            {schema.formula}
                        </p>
                    </div>
                </div>

                {records.data.length ? (
                    <>
                        <div className="table-wrapper">
                            <table className="data-table">
                                <thead>
                                    <tr>
                                        <th>ID Data</th>
                                        <th>Lokasi / Ekosistem</th>
                                        <th style={{ textAlign: 'right' }}>Kuantitas</th>
                                        <th style={{ textAlign: 'right' }}>Harga</th>
                                        <th style={{ textAlign: 'right' }}>Luas (ha)</th>
                                        <th style={{ textAlign: 'right' }}>Nilai / ha</th>
                                        <th style={{ textAlign: 'right' }}>Total Nilai</th>
                                        <th>Tahun</th>
                                        {canManage && <th>Aksi</th>}
                                    </tr>
                                </thead>
                                <tbody>
                                    {records.data.map((r) => (
                                        <tr key={r.id}>
                                            <td style={{ fontSize: 13, fontWeight: 600 }}>{r.record_code}</td>
                                            <td style={{ fontSize: 13 }}>{r.location}</td>
                                            <td style={{ textAlign: 'right', fontSize: 13 }}>
                                                {formatRupiah(r.quantity_value, 2)}
                                                <span style={{ color: 'var(--text-muted)', fontSize: 11, marginLeft: 4 }}>{r.quantity_unit}</span>
                                            </td>
                                            <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.unit_price, 0)}</td>
                                            <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(r.area_ha, 2)}</td>
                                            <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.value_per_ha, 0)}</td>
                                            <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: 'var(--success)' }}>Rp{formatRupiah(r.total_value, 0)}</td>
                                            <td style={{ fontSize: 13 }}>{r.period_year || '-'}</td>
                                            {canManage && (
                                                <td>
                                                    <div style={{ display: 'flex', gap: 4 }}>
                                                        <Link href={route('admin.modules.ecosystem.edit', [project.id, schema.key, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                        <ConfirmDeleteButton
                                                            href={route('admin.modules.ecosystem.destroy', [project.id, schema.key, r.id])}
                                                            message={`Hapus data ${r.record_code}?`}
                                                        />
                                                    </div>
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination paginator={records} />
                    </>
                ) : (
                    <div style={{ textAlign: 'center', padding: '32px 20px' }}>
                        <p style={{ color: 'var(--text-muted)', marginBottom: 14 }}>Belum ada data {schema.name}.</p>
                        {canManage && (
                            <Link href={route('admin.modules.ecosystem.create', [project.id, schema.key])} className="btn btn-sm btn-primary">
                                + Tambah Data Pertama
                            </Link>
                        )}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
