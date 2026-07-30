import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import CurrencyInput from '../../../Components/ui/CurrencyInput';

const CATEGORIES = [
    ['direct_cost', 'Direct Cost'],
    ['indirect_cost', 'Indirect Cost'],
];
const SUBCATEGORIES = ['investment', 'operation_maintenance', 'opportunity_cost', 'externality', 'other'];

export default function Edit({ project, cost }) {
    const { data, setData, put, processing, errors } = useForm({
        category: cost.category,
        subcategory: cost.subcategory,
        description: cost.description || '',
        value: cost.value,
        payment_type: cost.payment_type || '',
        calculation_notes: cost.calculation_notes || '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.costs.update', [project.id, cost.id]));
    }

    return (
        <AdminLayout title="Edit Cost">
            <Head title="Edit Cost" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
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
                                <CurrencyInput value={data.value} onChange={(v) => setData('value', v)} required />
                                {errors.value && <p className="form-error">{errors.value}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Tipe Pembayaran</label>
                                <input type="text" value={data.payment_type} onChange={(e) => setData('payment_type', e.target.value)} className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Perbarui</button>
                            <Link href={route('admin.projects.show', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
