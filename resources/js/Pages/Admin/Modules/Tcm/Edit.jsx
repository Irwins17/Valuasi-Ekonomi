import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import CurrencyInput from '../../../../Components/ui/CurrencyInput';

export default function Edit({ project, tcmData }) {
    const { data, setData, put, processing, errors } = useForm({
        distance: tcmData.distance,
        visit_frequency: tcmData.visit_frequency,
        transportation_cost: tcmData.transportation_cost,
        time_cost: tcmData.time_cost,
        origin_location: tcmData.origin_location || '',
        respondent_category: tcmData.respondent_category || '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.modules.tcm.update', [project.id, tcmData.id]));
    }

    return (
        <AdminLayout title="Edit Data TCM">
            <Head title="Edit Data TCM" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.modules.tcm.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Jarak (km)</label>
                                <input type="number" value={data.distance} onChange={(e) => setData('distance', e.target.value)} required step="0.01" className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Frekuensi Kunjungan</label>
                                <input type="number" value={data.visit_frequency} onChange={(e) => setData('visit_frequency', e.target.value)} required min="1" className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Biaya Transportasi (Rp)</label>
                                <CurrencyInput value={data.transportation_cost} onChange={(v) => setData('transportation_cost', v)} required />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Biaya Waktu (Rp)</label>
                                <CurrencyInput value={data.time_cost} onChange={(v) => setData('time_cost', v)} required />
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Asal Lokasi</label>
                            <input type="text" value={data.origin_location} onChange={(e) => setData('origin_location', e.target.value)} className="form-input" />
                        </div>
                        <div className="form-group">
                            <label className="form-label">Kategori</label>
                            <input type="text" value={data.respondent_category} onChange={(e) => setData('respondent_category', e.target.value)} className="form-input" />
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Perbarui</button>
                            <Link href={route('admin.modules.tcm.index', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
