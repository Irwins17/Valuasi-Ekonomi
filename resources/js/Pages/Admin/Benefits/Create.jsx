import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import CurrencyInput from '../../../Components/ui/CurrencyInput';

const CATEGORIES = [
    ['direct_use', 'Direct Use'],
    ['indirect_use', 'Indirect Use'],
    ['non_use', 'Non-Use'],
];
const SUBCATEGORIES = ['production', 'tourism', 'recreation', 'water_regulation', 'carbon_sequestration', 'existence_value', 'bequest_value'];
const METHODS = ['EOP', 'TCM', 'CVM', 'RC', 'Manual'];
const DATA_SOURCES = ['eop', 'tcm', 'cvm', 'manual', 'literature'];

export default function Create({ project }) {
    const { data, setData, post, processing, errors } = useForm({
        category: 'direct_use',
        subcategory: 'production',
        description: '',
        value: '',
        method_used: 'EOP',
        data_source: 'eop',
        calculation_notes: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.benefits.store', project.id));
    }

    return (
        <AdminLayout title="Tambah Benefit">
            <Head title="Tambah Benefit" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali ke Proyek</Link>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>Tambah Manfaat — {project.name}</h3>
                    <form onSubmit={submit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Kategori</label>
                                <select value={data.category} onChange={(e) => setData('category', e.target.value)} className="form-input" required>
                                    {CATEGORIES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                                </select>
                                {errors.category && <p className="form-error">{errors.category}</p>}
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
                            <input type="text" value={data.description} onChange={(e) => setData('description', e.target.value)} required className="form-input" placeholder="Deskripsi manfaat" />
                            {errors.description && <p className="form-error">{errors.description}</p>}
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Nilai (Rp)</label>
                                <CurrencyInput value={data.value} onChange={(v) => setData('value', v)} required placeholder="50.000" />
                                {errors.value && <p className="form-error">{errors.value}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Metode</label>
                                <select value={data.method_used} onChange={(e) => setData('method_used', e.target.value)} className="form-input">
                                    {METHODS.map((m) => <option key={m} value={m}>{m}</option>)}
                                </select>
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Sumber Data</label>
                            <select value={data.data_source} onChange={(e) => setData('data_source', e.target.value)} className="form-input" required>
                                {DATA_SOURCES.map((ds) => <option key={ds} value={ds}>{ds.replace(/^./, (c) => c.toUpperCase())}</option>)}
                            </select>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Catatan Perhitungan</label>
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
