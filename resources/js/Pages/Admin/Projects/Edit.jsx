import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import BoundarySourcePicker from '../../../Components/map/BoundarySourcePicker';
import { ECOSYSTEM_OBJECT_TYPES } from '../../../lib/ecosystemObjectTypes';

const STATUSES = ['draft', 'in_progress', 'completed', 'published'];

export default function Edit({ project }) {
    const { data, setData, put, processing, errors } = useForm({
        name: project.name || '',
        description: project.description || '',
        location: project.location || '',
        province: project.province || '',
        ecosystem_object_type: project.ecosystem_object_type || '',
        status: project.status,
        latitude: project.latitude ?? '',
        longitude: project.longitude ?? '',
        boundary_geojson: project.boundary_geojson ?? null,
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.projects.update', project.id));
    }

    return (
        <AdminLayout title="Edit Proyek">
            <Head title="Edit Proyek" />

            <div className="animate-fade-up">
                <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Kode Proyek</label>
                                <input type="text" value={project.code} className="form-input" disabled style={{ background: 'var(--surface-alt)' }} />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Nama Proyek</label>
                                <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="form-input" />
                                {errors.name && <p className="form-error">{errors.name}</p>}
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Deskripsi</label>
                            <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="form-input" rows={4} />
                        </div>
                        <div className="form-group">
                            <label className="form-label">Jenis Objek Ekosistem</label>
                            <select value={data.ecosystem_object_type} onChange={(e) => setData('ecosystem_object_type', e.target.value)} className="form-input">
                                <option value="">-- Pilih jenis objek ekosistem --</option>
                                {ECOSYSTEM_OBJECT_TYPES.map((t) => (
                                    <option key={t.value} value={t.value}>{t.label}</option>
                                ))}
                            </select>
                            {errors.ecosystem_object_type && <p className="form-error">{errors.ecosystem_object_type}</p>}
                        </div>

                        <div className="form-group">
                            <label className="form-label">Status</label>
                            <select value={data.status} onChange={(e) => setData('status', e.target.value)} className="form-input">
                                {STATUSES.map((s) => (
                                    <option key={s} value={s}>{s.replace('_', ' ').replace(/^./, (c) => c.toUpperCase())}</option>
                                ))}
                            </select>
                        </div>

                        <div className="form-group">
                            <label className="form-label">Batas Area & Wilayah</label>
                            <BoundarySourcePicker
                                value={data.boundary_geojson}
                                province={data.province}
                                onProvinceChange={(name) => setData('province', name)}
                                height={560}
                                onParsed={(geojson, centroid, label) => {
                                    setData('boundary_geojson', geojson);
                                    setData('latitude', centroid.lat.toFixed(6));
                                    setData('longitude', centroid.lng.toFixed(6));
                                    if (label) setData('location', label);
                                }}
                                onClear={() => {
                                    setData('boundary_geojson', null);
                                    setData('latitude', '');
                                    setData('longitude', '');
                                }}
                            />
                            {errors.province && <p className="form-error">{errors.province}</p>}
                            <p className="form-hint">Pilih wilayah administratif untuk menggambar batas area otomatis, atau unggah file SHP manual.</p>
                        </div>

                        <div className="form-group">
                            <label className="form-label">Lokasi</label>
                            <input type="text" value={data.location} onChange={(e) => setData('location', e.target.value)} required className="form-input" />
                            {errors.location && <p className="form-error">{errors.location}</p>}
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
