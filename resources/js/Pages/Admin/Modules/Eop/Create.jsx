import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import CurrencyInput from '../../../../Components/ui/CurrencyInput';

export default function Create({ project }) {
    const { data, setData, post, processing, errors } = useForm({
        commodity_name: '',
        production_before: '',
        production_after: '',
        unit: '',
        market_price: '',
        impact_type: 'positive',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.modules.eop.store', project.id));
    }

    return (
        <AdminLayout title="Tambah Data EOP">
            <Head title="Tambah Data EOP" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.modules.eop.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>Form Input EOP — {project.name}</h3>
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">Nama Komoditas</label>
                            <input type="text" value={data.commodity_name} onChange={(e) => setData('commodity_name', e.target.value)} required className="form-input" placeholder="Contoh: Padi, Ikan, Kayu" />
                            {errors.commodity_name && <p className="form-error">{errors.commodity_name}</p>}
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Produksi Sebelum</label>
                                <input type="number" value={data.production_before} onChange={(e) => setData('production_before', e.target.value)} required step="0.01" className="form-input" placeholder="0.00" />
                                {errors.production_before && <p className="form-error">{errors.production_before}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Produksi Sesudah</label>
                                <input type="number" value={data.production_after} onChange={(e) => setData('production_after', e.target.value)} required step="0.01" className="form-input" placeholder="0.00" />
                                {errors.production_after && <p className="form-error">{errors.production_after}</p>}
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Satuan</label>
                                <input type="text" value={data.unit} onChange={(e) => setData('unit', e.target.value)} required className="form-input" placeholder="Ton, Kg, m³" />
                                {errors.unit && <p className="form-error">{errors.unit}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Harga Pasar (Rp)</label>
                                <CurrencyInput value={data.market_price} onChange={(v) => setData('market_price', v)} required placeholder="50.000" />
                                {errors.market_price && <p className="form-error">{errors.market_price}</p>}
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Tipe Dampak</label>
                            <select value={data.impact_type} onChange={(e) => setData('impact_type', e.target.value)} className="form-input" required>
                                <option value="positive">Positif (Keuntungan)</option>
                                <option value="negative">Negatif (Kerugian)</option>
                            </select>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan Data</button>
                            <Link href={route('admin.modules.eop.index', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
