import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ prices, projects, scope }) {
    function changeScope(e) {
        router.get(route('admin.master.prices.index'), { scope: e.target.value }, { preserveState: true });
    }

    return (
        <AdminLayout title="Harga Pasar" subtitle="Master data harga pasar komoditas">
            <Head title="Harga Pasar" />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 16, marginBottom: 20, flexWrap: 'wrap' }}>
                <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' }}>
                    <label style={{ fontSize: 13, color: 'var(--text-secondary)', fontWeight: 500 }}>Cakupan:</label>
                    <select value={scope} onChange={changeScope} className="form-input" style={{ width: 'auto', padding: '6px 30px 6px 10px', fontSize: 13 }}>
                        <option value="all">Semua Proyek/Daerah</option>
                        <option value="global">Umum (Global)</option>
                        <optgroup label="Proyek Spesifik">
                            {projects.map((proj) => (
                                <option key={proj.id} value={proj.id}>{proj.name}</option>
                            ))}
                        </optgroup>
                    </select>
                    {scope !== 'all' && <Link href={route('admin.master.prices.index')} className="btn btn-sm btn-ghost">Reset</Link>}
                </div>
                <Link href={route('admin.master.prices.create')} className="btn btn-sm btn-primary">+ Tambah Harga</Link>
            </div>

            <div className="card">
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Komoditas</th><th>Cakupan</th><th>Satuan</th><th style={{ textAlign: 'right' }}>Harga (Rp)</th><th>Tahun</th><th>Sumber</th><th style={{ textAlign: 'center' }}>Aksi</th></tr></thead>
                        <tbody>
                            {prices.data.length ? (
                                prices.data.map((p) => (
                                    <tr key={p.id}>
                                        <td style={{ fontWeight: 600 }}>{p.commodity_name}</td>
                                        <td>
                                            {p.is_global ? (
                                                <span className="badge badge-gray">Umum (Global)</span>
                                            ) : (
                                                <span className="badge badge-primary">{p.project?.name || `Proyek #${p.project_id}`}</span>
                                            )}
                                        </td>
                                        <td>{p.unit}</td>
                                        <td style={{ textAlign: 'right', fontWeight: 700, color: 'var(--primary)' }}>Rp{formatRupiah(p.price)}</td>
                                        <td><span className="badge badge-info">{p.year}</span></td>
                                        <td style={{ fontSize: 13, color: 'var(--text-secondary)' }}>{p.source || '-'}</td>
                                        <td style={{ textAlign: 'center' }}>
                                            <div style={{ display: 'flex', gap: 4, justifyContent: 'center' }}>
                                                <Link href={route('admin.master.prices.edit', p.id)} className="btn btn-sm btn-ghost">Edit</Link>
                                                <ConfirmDeleteButton href={route('admin.master.prices.destroy', p.id)} />
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr><td colSpan={7} style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>Belum ada data harga pasar untuk cakupan ini.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination paginator={prices} />
            </div>
        </AdminLayout>
    );
}
