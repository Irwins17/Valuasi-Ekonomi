import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../../Layouts/AdminLayout';
import CurrencyInput from '../../../../Components/ui/CurrencyInput';

const EDUCATION_LEVELS = ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'];

export default function Create({ project }) {
    const { data, setData, post, processing, errors } = useForm({
        respondent_id: '',
        willing_to_pay: '1',
        wtp: '',
        reason_if_unwilling: '',
        household_size: '',
        household_income: '',
        education_level: '',
        respondent_location: '',
    });

    const willing = data.willing_to_pay === '1';

    function submit(e) {
        e.preventDefault();
        post(route('admin.modules.cvm.store', project.id));
    }

    return (
        <AdminLayout title="Tambah Data CVM">
            <Head title="Tambah Data CVM" />

            <div style={{ maxWidth: 700, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.modules.cvm.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>Form Input CVM — {project.name}</h3>
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">ID Responden (Nomor)</label>
                            <input type="number" value={data.respondent_id} onChange={(e) => setData('respondent_id', e.target.value)} required min="1" className="form-input" placeholder="2001" />
                            {errors.respondent_id && <p className="form-error">{errors.respondent_id}</p>}
                        </div>
                        <div className="form-group">
                            <label className="form-label">Bersedia Membayar?</label>
                            <select value={data.willing_to_pay} onChange={(e) => setData('willing_to_pay', e.target.value)} className="form-input" required>
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>

                        {willing ? (
                            <div className="form-group">
                                <label className="form-label">Nilai WTP (Rp)</label>
                                <CurrencyInput value={data.wtp} onChange={(v) => setData('wtp', v)} placeholder="50.000" />
                                {errors.wtp && <p className="form-error">{errors.wtp}</p>}
                            </div>
                        ) : (
                            <div className="form-group">
                                <label className="form-label">Alasan Tidak Bersedia</label>
                                <textarea value={data.reason_if_unwilling} onChange={(e) => setData('reason_if_unwilling', e.target.value)} className="form-input" rows={2} />
                                {errors.reason_if_unwilling && <p className="form-error">{errors.reason_if_unwilling}</p>}
                            </div>
                        )}

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Jumlah Anggota RT</label>
                                <input type="number" value={data.household_size} onChange={(e) => setData('household_size', e.target.value)} min="1" className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Pendapatan RT (Rp)</label>
                                <CurrencyInput value={data.household_income} onChange={(v) => setData('household_income', v)} placeholder="3.000.000" />
                            </div>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Tingkat Pendidikan</label>
                                <select value={data.education_level} onChange={(e) => setData('education_level', e.target.value)} className="form-input">
                                    <option value="">-- Pilih --</option>
                                    {EDUCATION_LEVELS.map((lvl) => <option key={lvl} value={lvl}>{lvl}</option>)}
                                </select>
                            </div>
                            <div className="form-group">
                                <label className="form-label">Lokasi Responden</label>
                                <input type="text" value={data.respondent_location} onChange={(e) => setData('respondent_location', e.target.value)} className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan</button>
                            <Link href={route('admin.modules.cvm.index', project.id)} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
