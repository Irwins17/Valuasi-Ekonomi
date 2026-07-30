import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import CurrencyInput from '../../../../Components/ui/CurrencyInput';

export default function Edit({ project, cvmData }) {
    const { data, setData, put, processing, errors } = useForm({
        willing_to_pay: cvmData.willing_to_pay ? '1' : '0',
        wtp: cvmData.wtp ?? '',
        reason_if_unwilling: cvmData.reason_if_unwilling || '',
        household_size: cvmData.household_size ?? '',
        household_income: cvmData.household_income ?? '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('admin.modules.cvm.update', [project.id, cvmData.id]));
    }

    return (
        <AdminLayout title="Edit Data CVM">
            <Head title="Edit Data CVM" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.modules.cvm.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">Bersedia Membayar?</label>
                            <select value={data.willing_to_pay} onChange={(e) => setData('willing_to_pay', e.target.value)} className="form-input" required>
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Nilai WTP (Rp)</label>
                            <CurrencyInput value={data.wtp} onChange={(v) => setData('wtp', v)} />
                            {errors.wtp && <p className="form-error">{errors.wtp}</p>}
                        </div>
                        <div className="form-group">
                            <label className="form-label">Alasan (jika tidak bersedia)</label>
                            <textarea value={data.reason_if_unwilling} onChange={(e) => setData('reason_if_unwilling', e.target.value)} className="form-input" rows={2} />
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Jumlah Anggota RT</label>
                                <input type="number" value={data.household_size} onChange={(e) => setData('household_size', e.target.value)} className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Pendapatan RT (Rp)</label>
                                <CurrencyInput value={data.household_income} onChange={(v) => setData('household_income', v)} />
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Perbarui</button>
                            <Link href={route('admin.modules.cvm.index', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
