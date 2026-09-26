import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import BoundarySourcePicker from '../../../Components/map/BoundarySourcePicker';
import { ECOSYSTEM_OBJECT_TYPES } from '../../../lib/ecosystemObjectTypes';

const CATEGORY_COLORS = {
    provisioning: '#10b981',
    regulating: '#3b82f6',
    supporting: '#8b5cf6',
    cultural: '#f59e0b',
};

export default function Create({ moduleCatalog = [], serviceCategories = {} }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        description: '',
        location: '',
        province: '',
        ecosystem_object_type: '',
        latitude: '',
        longitude: '',
        boundary_geojson: null,
        selected_modules: [],
    });

    const groups = Object.keys(serviceCategories).map((key) => ({
        key,
        label: serviceCategories[key],
        color: CATEGORY_COLORS[key] || 'var(--text-muted)',
        modules: moduleCatalog.filter((m) => m.service_category === key),
    }));

    function toggleModule(code) {
        setData('selected_modules', data.selected_modules.includes(code)
            ? data.selected_modules.filter((c) => c !== code)
            : [...data.selected_modules, code]);
    }

    function selectAll() {
        setData('selected_modules', moduleCatalog.map((m) => m.code));
    }

    function selectNone() {
        setData('selected_modules', []);
    }

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
                            <label className="form-label">Jenis Objek Ekosistem</label>
                            <select value={data.ecosystem_object_type} onChange={(e) => setData('ecosystem_object_type', e.target.value)} className="form-input">
                                <option value="">-- Pilih jenis objek ekosistem --</option>
                                {ECOSYSTEM_OBJECT_TYPES.map((t) => (
                                    <option key={t.value} value={t.value}>{t.label}</option>
                                ))}
                            </select>
                            {errors.ecosystem_object_type && <p className="form-error">{errors.ecosystem_object_type}</p>}
                            <p className="form-hint">Objek utama yang dinilai dalam proyek ini (Langkah 2 — Menentukan Objek dan Batas Wilayah).</p>
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
                            <input type="text" value={data.location} onChange={(e) => setData('location', e.target.value)} required className="form-input" placeholder="Terisi otomatis dari wilayah yang dipilih, atau isi manual" />
                            {errors.location && <p className="form-error">{errors.location}</p>}
                        </div>

                        <div className="form-group">
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                                <label className="form-label" style={{ marginBottom: 0 }}>Modul Valuasi yang Digunakan</label>
                                <div style={{ display: 'flex', gap: 8 }}>
                                    <button type="button" onClick={selectAll} className="btn btn-sm btn-ghost">Pilih Semua</button>
                                    <button type="button" onClick={selectNone} className="btn btn-sm btn-ghost">Kosongkan</button>
                                </div>
                            </div>
                            <p className="form-hint" style={{ marginTop: 0, marginBottom: 12 }}>
                                Pilih modul yang relevan dengan proyek ini. Hanya modul yang dipilih yang akan tampil di Status Modul — sisanya tetap bisa diaktifkan belakangan lewat halaman Modul Valuasi.
                            </p>
                            {groups.map((group) => (
                                <div key={group.key} style={{ marginBottom: 16 }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
                                        <span style={{ width: 8, height: 8, borderRadius: '50%', background: group.color }}></span>
                                        <span style={{ fontSize: 13, fontWeight: 700 }}>{group.label}</span>
                                    </div>
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
                                        {group.modules.map((m) => (
                                            <label key={m.code} style={{ display: 'flex', alignItems: 'flex-start', gap: 8, padding: 10, border: '1px solid var(--border-light)', borderRadius: 'var(--radius-sm)', cursor: 'pointer', background: data.selected_modules.includes(m.code) ? 'var(--surface-alt)' : 'transparent' }}>
                                                <input
                                                    type="checkbox"
                                                    checked={data.selected_modules.includes(m.code)}
                                                    onChange={() => toggleModule(m.code)}
                                                    style={{ width: 16, height: 16, marginTop: 2, accentColor: 'var(--primary)', cursor: 'pointer' }}
                                                />
                                                <span>
                                                    <span style={{ display: 'block', fontSize: 13, fontWeight: 600 }}>{m.name}</span>
                                                    <span style={{ display: 'block', fontSize: 11, color: 'var(--text-muted)', marginTop: 2 }}>{m.description}</span>
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                            ))}
                            {errors.selected_modules && <p className="form-error">{errors.selected_modules}</p>}
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
