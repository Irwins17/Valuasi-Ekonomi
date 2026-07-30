import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ coefficients }) {
    return (
        <AdminLayout title="Koefisien Lingkungan" subtitle="Master data koefisien lingkungan">
            <Head title="Koefisien Lingkungan" />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                <div></div>
                <Link href={route('admin.master.coefficients.create')} className="btn btn-sm btn-primary">+ Tambah Koefisien</Link>
            </div>

            <div className="card">
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th style={{ textAlign: 'right' }}>Nilai</th><th>Satuan</th><th>Sumber</th><th style={{ textAlign: 'center' }}>Aksi</th></tr></thead>
                        <tbody>
                            {coefficients.data.length ? (
                                coefficients.data.map((c) => (
                                    <tr key={c.id}>
                                        <td style={{ fontFamily: 'monospace', fontSize: 13 }}>{c.code}</td>
                                        <td style={{ fontWeight: 600 }}>{c.name}</td>
                                        <td><span className="badge badge-info">{c.type}</span></td>
                                        <td style={{ textAlign: 'right', fontWeight: 700 }}>{formatRupiah(c.value, 4)}</td>
                                        <td style={{ fontSize: 13 }}>{c.unit}</td>
                                        <td style={{ fontSize: 13, color: 'var(--text-secondary)' }}>{c.source || '-'}</td>
                                        <td style={{ textAlign: 'center' }}>
                                            <div style={{ display: 'flex', gap: 4, justifyContent: 'center' }}>
                                                <Link href={route('admin.master.coefficients.edit', c.id)} className="btn btn-sm btn-ghost">Edit</Link>
                                                <ConfirmDeleteButton href={route('admin.master.coefficients.destroy', c.id)} />
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr><td colSpan={7} style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>Belum ada data koefisien.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination paginator={coefficients} />
            </div>
        </AdminLayout>
    );
}
