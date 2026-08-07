import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import CurrencyInput from '../../../../Components/ui/CurrencyInput';

export default function Edit({ project, eopData }) {
    const { data, setData, put, processing, errors } = useForm({
        commodity_name: eopData.commodity_name,
        production_before: eopData.production_before,
        production_after: eopData.production_after,
        unit: eopData.unit,
        market_price: eopData.market_price,
        impact_type: eopData.impact_type,
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.modules.eop.update', [project.id, eopData.id]));
    }

    return (
        <AdminLayout title="Edit Data EOP">
            <Head title="Edit Data EOP" />

            <div className="animate-fade-up">
                <Link href={route('admin.modules.eop.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">Nama Komoditas</label>
                            <input type="text" value={data.commodity_name} onChange={(e) => setData('commodity_name', e.target.value)} required className="form-input" />
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Produksi Sebelum</label>
                                <input type="number" value={data.production_before} onChange={(e) => setData('production_before', e.target.value)} required step="0.01" className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Produksi Sesudah</label>
                                <input type="number" value={data.production_after} onChange={(e) => setData('production_after', e.target.value)} required step="0.01" className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Satuan</label>
                                <input type="text" value={data.unit} onChange={(e) => setData('unit', e.target.value)} required className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Harga Pasar (Rp)</label>
                                <CurrencyInput value={data.market_price} onChange={(v) => setData('market_price', v)} required />
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Tipe Dampak</label>
                            <select value={data.impact_type} onChange={(e) => setData('impact_type', e.target.value)} className="form-input" required>
                                <option value="positive">Positif</option>
                                <option value="negative">Negatif</option>
                            </select>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Perbarui</button>
                            <Link href={route('admin.modules.eop.index', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
