import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';

export default function Edit({ coefficient }) {
    const { data, setData, put, processing, errors } = useForm({
        code: coefficient.code,
        name: coefficient.name,
        description: coefficient.description || '',
        value: coefficient.value,
        unit: coefficient.unit,
        type: coefficient.type,
        source: coefficient.source || '',
        year: coefficient.year || '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.master.coefficients.update', coefficient.id));
    }

    return (
        <AdminLayout title="Edit Koefisien">
            <Head title="Edit Koefisien" />

            <div className="animate-fade-up">
                <Link href={route('admin.master.coefficients.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Kode</label>
                                <input type="text" value={data.code} onChange={(e) => setData('code', e.target.value)} required className="form-input" />
                                {errors.code && <p className="form-error">{errors.code}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Nama</label>
                                <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="form-input" />
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Deskripsi</label>
                            <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="form-input" rows={2} />
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Nilai</label>
                                <input type="number" value={data.value} onChange={(e) => setData('value', e.target.value)} required step="0.0001" className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Satuan</label>
                                <input type="text" value={data.unit} onChange={(e) => setData('unit', e.target.value)} required className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Tipe</label>
                                <input type="text" value={data.type} onChange={(e) => setData('type', e.target.value)} required className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Sumber</label>
                                <input type="text" value={data.source} onChange={(e) => setData('source', e.target.value)} className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Tahun</label>
                                <input type="number" value={data.year} onChange={(e) => setData('year', e.target.value)} className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Perbarui</button>
                            <Link href={route('admin.master.coefficients.index')} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
