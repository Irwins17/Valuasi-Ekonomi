import { useEffect, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import BarChart from '../../../Components/charts/BarChart';
import { toNumber } from '../../../lib/format';

function fmtTriliun(n) {
    return 'Rp ' + (toNumber(n) / 1e9).toFixed(2) + 'T';
}

export default function Index({ projects }) {
    const [projectId, setProjectId] = useState('');
    const [inflation, setInflation] = useState(0);
    const [price, setPrice] = useState(0);
    const [discount, setDiscount] = useState(0);
    const [result, setResult] = useState(null);

    useEffect(() => {
        if (!projectId) {
            setResult(null);
            return;
        }

        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        const params = new URLSearchParams({
            project_id: projectId,
            inflation_rate: inflation,
            price_adjustment: price,
            discount_rate: discount,
        });

        fetch(`/admin/sensitivity/simulate?${params}`, {
            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        })
            .then((r) => r.json())
            .then(setResult)
            .catch((err) => console.error('Gagal memuat simulasi sensitivitas:', err));
    }, [projectId, inflation, price, discount]);

    function resetAll() {
        setInflation(0);
        setPrice(0);
        setDiscount(0);
    }

    const tevPct = toNumber(result?.changes?.tev_pct);
    const bcrPct = toNumber(result?.changes?.bcr_pct);

    return (
        <AdminLayout title="Analisis Sensitivitas dan Validasi" subtitle="Langkah 9 — Uji Sensitivitas, Uji Asumsi, Validasi Stakeholder">
            <Head title="Analisis Sensitivitas dan Validasi" />

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: 24 }} className="animate-fade-up">
                <div className="card" style={{ position: 'sticky', top: 100, alignSelf: 'start' }}>
                    <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 20 }}>Parameter Simulasi</h3>

                    <div className="form-group">
                        <label className="form-label">Pilih Proyek</label>
                        <select value={projectId} onChange={(e) => setProjectId(e.target.value)} className="form-input">
                            <option value="">-- Pilih Proyek --</option>
                            {projects.map((p) => (
                                <option key={p.id} value={p.id}>{p.name}</option>
                            ))}
                        </select>
                    </div>

                    {projectId && (
                        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 20 }}>
                            <Link href={route('admin.assumptions.index', projectId)} className="btn btn-sm btn-outline" style={{ flex: 1 }}>Uji Asumsi</Link>
                            <Link href={route('admin.stakeholder-validations.index', projectId)} className="btn btn-sm btn-outline" style={{ flex: 1 }}>Validasi Stakeholder</Link>
                        </div>
                    )}

                    <div className="form-group">
                        <label className="form-label">Tingkat Inflasi: <span>{inflation}</span>%</label>
                        <input type="range" min="-30" max="30" value={inflation} onChange={(e) => setInflation(Number(e.target.value))} style={{ width: '100%', accentColor: 'var(--primary)' }} />
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 11, color: 'var(--text-muted)' }}><span>-30%</span><span>0%</span><span>+30%</span></div>
                    </div>

                    <div className="form-group">
                        <label className="form-label">Penyesuaian Harga Pasar: <span>{price}</span>%</label>
                        <input type="range" min="-30" max="30" value={price} onChange={(e) => setPrice(Number(e.target.value))} style={{ width: '100%', accentColor: 'var(--secondary)' }} />
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 11, color: 'var(--text-muted)' }}><span>-30%</span><span>0%</span><span>+30%</span></div>
                    </div>

                    <div className="form-group">
                        <label className="form-label">Discount Rate: <span>{discount}</span>%</label>
                        <input type="range" min="0" max="15" value={discount} onChange={(e) => setDiscount(Number(e.target.value))} style={{ width: '100%', accentColor: 'var(--accent)' }} />
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 11, color: 'var(--text-muted)' }}><span>0%</span><span>15%</span></div>
                    </div>

                    <button onClick={resetAll} className="btn btn-sm btn-ghost" style={{ width: '100%' }}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M3 12a9 9 0 0115.36-6.36L21 8" /><path d="M21 3v5h-5" /><path d="M21 12a9 9 0 01-15.36 6.36L3 16" /><path d="M21 21v-5h-5" /></svg>
                        Reset Semua
                    </button>
                </div>

                <div>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2,1fr)', gap: 16, marginBottom: 24 }}>
                        <div className="stat-card"><div className="stat-label">TEV Original</div><div className="stat-value" style={{ fontSize: 20 }}>{result ? fmtTriliun(result.original.tev) : '-'}</div></div>
                        <div className="stat-card"><div className="stat-label">TEV Adjusted</div><div className="stat-value" style={{ fontSize: 20, color: 'var(--primary)' }}>{result ? fmtTriliun(result.adjusted.tev) : '-'}</div></div>
                        <div className="stat-card"><div className="stat-label">BCR Original</div><div className="stat-value" style={{ fontSize: 20 }}>{result ? toNumber(result.original.bcr).toFixed(4) : '-'}</div></div>
                        <div className="stat-card"><div className="stat-label">BCR Adjusted</div><div className="stat-value" style={{ fontSize: 20, color: 'var(--primary)' }}>{result ? toNumber(result.adjusted.bcr).toFixed(4) : '-'}</div></div>
                    </div>

                    <div className="card" style={{ marginBottom: 24 }}>
                        <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 16 }}>Perbandingan Original vs Adjusted</h3>
                        <div style={{ height: 300 }}>
                            {result && (
                                <BarChart
                                    labels={['Benefits', 'Costs', 'TEV']}
                                    datasets={[
                                        { label: 'Original', data: [toNumber(result.original.benefits) / 1e9, toNumber(result.original.costs) / 1e9, toNumber(result.original.tev) / 1e9], backgroundColor: '#6366f1', borderRadius: 6 },
                                        { label: 'Adjusted', data: [toNumber(result.adjusted.benefits) / 1e9, toNumber(result.adjusted.costs) / 1e9, toNumber(result.adjusted.tev) / 1e9], backgroundColor: '#06d6a0', borderRadius: 6 },
                                    ]}
                                />
                            )}
                        </div>
                    </div>

                    <div className="card">
                        <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 16 }}>Perubahan (%)</h3>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div style={{ padding: 16, borderRadius: 'var(--radius-sm)', background: 'var(--primary-50)', textAlign: 'center' }}>
                                <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Δ TEV</div>
                                <div style={{ fontSize: 24, fontWeight: 800, color: tevPct >= 0 ? '#10b981' : '#ef4444' }}>{tevPct >= 0 ? '+' : ''}{tevPct}%</div>
                            </div>
                            <div style={{ padding: 16, borderRadius: 'var(--radius-sm)', background: '#ecfdf5', textAlign: 'center' }}>
                                <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Δ BCR</div>
                                <div style={{ fontSize: 24, fontWeight: 800, color: bcrPct >= 0 ? '#10b981' : '#ef4444' }}>{bcrPct >= 0 ? '+' : ''}{bcrPct}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
