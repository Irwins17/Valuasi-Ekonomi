import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

const STATUSES = ['draft', 'in_progress', 'completed', 'published'];

export default function Edit({ project }) {
    const { data, setData, put, processing, errors } = useForm({
        name: project.name || '',
        description: project.description || '',
        location: project.location || '',
        status: project.status,
        latitude: project.latitude ?? '',
        longitude: project.longitude ?? '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.projects.update', project.id));
    }

    return (
        <AdminLayout title="Edit Proyek">
            <Head title="Edit Proyek" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">Kode Proyek</label>
                            <input type="text" value={project.code} className="form-input" disabled style={{ background: 'var(--surface-alt)' }} />
                        </div>
                        <div className="form-group">
                            <label className="form-label">Nama Proyek</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="form-input" />
                            {errors.name && <p className="form-error">{errors.name}</p>}
                        </div>
                        <div className="form-group">
                            <label className="form-label">Deskripsi</label>
                            <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="form-input" rows={4} />
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Lokasi</label>
                                <input type="text" value={data.location} onChange={(e) => setData('location', e.target.value)} required className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Status</label>
                                <select value={data.status} onChange={(e) => setData('status', e.target.value)} className="form-input">
                                    {STATUSES.map((s) => (
                                        <option key={s} value={s}>{s.replace('_', ' ').replace(/^./, (c) => c.toUpperCase())}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Latitude</label>
                                <input type="number" value={data.latitude} onChange={(e) => setData('latitude', e.target.value)} step="0.00001" className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Longitude</label>
                                <input type="number" value={data.longitude} onChange={(e) => setData('longitude', e.target.value)} step="0.00001" className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan Perubahan</button>
                            <Link href={route('admin.projects.show', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
