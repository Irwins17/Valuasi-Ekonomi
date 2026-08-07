import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import CurrencyInput from '../../../Components/ui/CurrencyInput';

const CATEGORIES = [
    ['direct_cost', 'Direct Cost'],
    ['indirect_cost', 'Indirect Cost'],
];
const SUBCATEGORIES = ['investment', 'operation_maintenance', 'opportunity_cost', 'externality', 'other'];

export default function Create({ project }) {
    const { data, setData, post, processing, errors } = useForm({
        category: 'direct_cost',
        subcategory: 'investment',
        description: '',
        value: '',
        payment_type: '',
        calculation_notes: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.costs.store', project.id));
    }

    return (
        <AdminLayout title="Tambah Cost">
            <Head title="Tambah Cost" />

            <div className="animate-fade-up">
                <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>Tambah Biaya — {project.name}</h3>
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Kategori</label>
                                <select value={data.category} onChange={(e) => setData('category', e.target.value)} className="form-input" required>
                                    {CATEGORIES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                                </select>
                            </div>
                            <div className="form-group">
                                <label className="form-label">Subkategori</label>
                                <select value={data.subcategory} onChange={(e) => setData('subcategory', e.target.value)} className="form-input" required>
                                    {SUBCATEGORIES.map((s) => <option key={s} value={s}>{s.replace('_', ' ').replace(/^./, (c) => c.toUpperCase())}</option>)}
                                </select>
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Deskripsi</label>
                            <input type="text" value={data.description} onChange={(e) => setData('description', e.target.value)} required className="form-input" />
                            {errors.description && <p className="form-error">{errors.description}</p>}
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Nilai (Rp)</label>
                                <CurrencyInput value={data.value} onChange={(v) => setData('value', v)} required placeholder="50.000" />
                                {errors.value && <p className="form-error">{errors.value}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Tipe Pembayaran</label>
                                <input type="text" value={data.payment_type} onChange={(e) => setData('payment_type', e.target.value)} className="form-input" placeholder="Investasi Awal / Tahunan" />
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Catatan</label>
                            <textarea value={data.calculation_notes} onChange={(e) => setData('calculation_notes', e.target.value)} className="form-input" rows={3} />
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan</button>
                            <Link href={route('admin.projects.show', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
