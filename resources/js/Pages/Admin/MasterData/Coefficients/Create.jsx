import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        description: '',
        value: '',
        unit: '',
        type: '',
        source: '',
        year: new Date().getFullYear(),
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.master.coefficients.store'));
    }

    return (
        <AdminLayout title="Tambah Koefisien">
            <Head title="Tambah Koefisien" />

            <div style={{ maxWidth: 600, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.master.coefficients.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Kode</label>
                                <input type="text" value={data.code} onChange={(e) => setData('code', e.target.value)} required className="form-input" placeholder="CARB-01" />
                                {errors.code && <p className="form-error">{errors.code}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Nama</label>
                                <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="form-input" placeholder="Serapan Karbon" />
                                {errors.name && <p className="form-error">{errors.name}</p>}
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
                                {errors.value && <p className="form-error">{errors.value}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Satuan</label>
                                <input type="text" value={data.unit} onChange={(e) => setData('unit', e.target.value)} required className="form-input" placeholder="ton CO₂/ha/th" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Tipe</label>
                                <input type="text" value={data.type} onChange={(e) => setData('type', e.target.value)} required className="form-input" placeholder="carbon, water, etc" />
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
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan</button>
                            <Link href={route('admin.master.coefficients.index')} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
