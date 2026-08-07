import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import BoundarySourcePicker from '../../../Components/map/BoundarySourcePicker';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        description: '',
        location: '',
        province: '',
        latitude: '',
        longitude: '',
        boundary_geojson: null,
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.projects.store'));
    }

    return (
        <AdminLayout title="Buat Proyek Baru">
            <Head title="Buat Proyek Baru" />

            <div className="animate-fade-up">
                <Link href={route('admin.projects.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali ke Daftar Proyek</Link>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 24 }}>Form Proyek Baru</h3>
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
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
                        </div>

                        <div className="form-group">
                            <label className="form-label">Deskripsi</label>
                            <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="form-input" rows={4} placeholder="Deskripsi proyek..." />
                        </div>

                        <div className="form-group">
                            <label className="form-label">Batas Area & Wilayah</label>
                            <BoundarySourcePicker
                                value={data.boundary_geojson}
                                province={data.province}
                                onProvinceChange={(name) => setData('province', name)}
                                height={420}
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
                            <input type="text" value={data.location} onChange={(e) => setData('location', e.target.value)} required className="form-input" placeholder="Terisi otomatis dari wilayah yang dipilih, atau isi manual" />
                            {errors.location && <p className="form-error">{errors.location}</p>}
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
