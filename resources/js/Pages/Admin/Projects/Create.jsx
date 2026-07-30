import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        description: '',
        location: '',
        latitude: '',
        longitude: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.projects.store'));
    }

    return (
        <AdminLayout title="Buat Proyek Baru">
            <Head title="Buat Proyek Baru" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.projects.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali ke Daftar Proyek</Link>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 24 }}>Form Proyek Baru</h3>
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">Kode Proyek</label>
                            <input type="text" value={data.code} onChange={(e) => setData('code', e.target.value)} required className="form-input" placeholder="PROJ-001" />
                            {errors.code && <p className="form-error">{errors.code}</p>}
                        </div>
                        <div className="form-group">
                            <label className="form-label">Nama Proyek</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="form-input" placeholder="Nama proyek valuasi" />
                            {errors.name && <p className="form-error">{errors.name}</p>}
                        </div>

                        <div className="form-group">
                            <label className="form-label">Deskripsi</label>
                            <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="form-input" rows={4} placeholder="Deskripsi proyek..." />
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Lokasi</label>
                                <input type="text" value={data.location} onChange={(e) => setData('location', e.target.value)} required className="form-input" placeholder="Lokasi geografis" />
                                {errors.location && <p className="form-error">{errors.location}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Latitude</label>
                                <input type="number" value={data.latitude} onChange={(e) => setData('latitude', e.target.value)} step="0.00001" className="form-input" placeholder="-6.200000" />
                            </div>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Longitude</label>
                                <input type="number" value={data.longitude} onChange={(e) => setData('longitude', e.target.value)} step="0.00001" className="form-input" placeholder="106.816666" />
                            </div>
                        </div>

                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Buat Proyek</button>
                            <Link href={route('admin.projects.index')} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
