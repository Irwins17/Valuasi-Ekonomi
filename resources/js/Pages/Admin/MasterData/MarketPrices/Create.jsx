import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import CurrencyInput from '../../../../Components/ui/CurrencyInput';

export default function Create({ projects }) {
    const { data, setData, post, processing, errors } = useForm({
        project_id: '',
        commodity_name: '',
        unit: '',
        price: '',
        year: new Date().getFullYear(),
        source: '',
        notes: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.master.prices.store'));
    }

    return (
        <AdminLayout title="Tambah Harga Pasar">
            <Head title="Tambah Harga Pasar" />

            <div className="animate-fade-up">
                <Link href={route('admin.master.prices.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">Cakupan Harga</label>
                            <select value={data.project_id} onChange={(e) => setData('project_id', e.target.value)} className="form-input">
                                <option value="">Umum (Global) — berlaku untuk semua proyek/daerah</option>
                                <optgroup label="Spesifik untuk Proyek/Daerah">
                                    {projects.map((proj) => (
                                        <option key={proj.id} value={proj.id}>{proj.name} ({proj.code})</option>
                                    ))}
                                </optgroup>
                            </select>
                            <p className="form-hint">Pilih "Umum" untuk harga acuan nasional, atau pilih proyek jika harga ini khusus daerah proyek tersebut.</p>
                            {errors.project_id && <p className="form-error">{errors.project_id}</p>}
                        </div>
                        <div className="form-group">
                            <label className="form-label">Nama Komoditas</label>
                            <input type="text" value={data.commodity_name} onChange={(e) => setData('commodity_name', e.target.value)} required className="form-input" />
                            {errors.commodity_name && <p className="form-error">{errors.commodity_name}</p>}
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Satuan</label>
                                <input type="text" value={data.unit} onChange={(e) => setData('unit', e.target.value)} required className="form-input" placeholder="Kg, Ton, m³" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Harga (Rp)</label>
                                <CurrencyInput value={data.price} onChange={(v) => setData('price', v)} required placeholder="50.000" />
                                {errors.price && <p className="form-error">{errors.price}</p>}
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Tahun</label>
                                <input type="number" value={data.year} onChange={(e) => setData('year', e.target.value)} required className="form-input" />
                                {errors.year && <p className="form-error">{errors.year}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Sumber</label>
                                <input type="text" value={data.source} onChange={(e) => setData('source', e.target.value)} className="form-input" placeholder="BPS, Kementan, dll" />
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Catatan</label>
                            <textarea value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="form-input" rows={2} />
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan</button>
                            <Link href={route('admin.master.prices.index')} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
