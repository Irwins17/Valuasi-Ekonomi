import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import CurrencyInput from '../../../../Components/ui/CurrencyInput';

export default function Create({ project }) {
    const { data, setData, post, processing, errors } = useForm({
        respondent_id: '',
        origin_location: '',
        distance: '',
        visit_frequency: '',
        transportation_cost: '',
        time_cost: '',
        respondent_category: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.modules.tcm.store', project.id));
    }

    return (
        <AdminLayout title="Tambah Data TCM">
            <Head title="Tambah Data TCM" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.modules.tcm.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>Form Input TCM — {project.name}</h3>
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">ID Responden (Nomor)</label>
                                <input type="number" value={data.respondent_id} onChange={(e) => setData('respondent_id', e.target.value)} required min="1" className="form-input" placeholder="1001" />
                                {errors.respondent_id && <p className="form-error">{errors.respondent_id}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Asal Lokasi</label>
                                <input type="text" value={data.origin_location} onChange={(e) => setData('origin_location', e.target.value)} className="form-input" placeholder="Kota asal" />
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Jarak (km)</label>
                                <input type="number" value={data.distance} onChange={(e) => setData('distance', e.target.value)} required step="0.01" min="0" className="form-input" />
                                {errors.distance && <p className="form-error">{errors.distance}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Frekuensi Kunjungan</label>
                                <input type="number" value={data.visit_frequency} onChange={(e) => setData('visit_frequency', e.target.value)} required min="1" className="form-input" />
                                {errors.visit_frequency && <p className="form-error">{errors.visit_frequency}</p>}
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Biaya Transportasi (Rp)</label>
                                <CurrencyInput value={data.transportation_cost} onChange={(v) => setData('transportation_cost', v)} required placeholder="50.000" />
                                {errors.transportation_cost && <p className="form-error">{errors.transportation_cost}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Biaya Waktu (Rp)</label>
                                <CurrencyInput value={data.time_cost} onChange={(v) => setData('time_cost', v)} required placeholder="10.000" />
                                {errors.time_cost && <p className="form-error">{errors.time_cost}</p>}
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Kategori Responden</label>
                            <input type="text" value={data.respondent_category} onChange={(e) => setData('respondent_category', e.target.value)} className="form-input" placeholder="Wisatawan / Penduduk Lokal" />
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan</button>
                            <Link href={route('admin.modules.tcm.index', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
