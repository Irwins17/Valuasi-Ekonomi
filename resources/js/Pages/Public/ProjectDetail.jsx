import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import DonutChart from '../../Components/charts/DonutChart';
import LeafletMap from '../../Components/map/LeafletMap';
import { toNumber, formatTriliun, formatRupiah } from '../../lib/format';

export default function ProjectDetail({ project, benefits, costs }) {
    const benefitTotals = {};
    benefits.forEach((b) => {
        benefitTotals[b.category] = (benefitTotals[b.category] || 0) + toNumber(b.value);
    });
    const benefitLabels = Object.keys(benefitTotals).map((k) => k.replace('_', ' '));
    const benefitData = Object.values(benefitTotals);

    const bcr = toNumber(project.bcr);
    const hasLocation = project.latitude && project.longitude;

    return (
        <GuestLayout>
            <Head title={`${project.name} — Valuasi Ekonomi`} />

            <section style={{ background: 'linear-gradient(135deg,#312e81,#4f46e5)', padding: '60px 0' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }} className="animate-fade-up">
                    <Link href={route('public.dashboard')} style={{ color: 'rgba(255,255,255,0.6)', fontSize: 13, textDecoration: 'none', display: 'flex', alignItems: 'center', gap: 6, marginBottom: 16 }}>
                        ← Kembali ke Dashboard
                    </Link>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <div>
                            <span style={{ fontFamily: 'monospace', fontSize: 13, color: 'rgba(255,255,255,0.5)' }}>{project.code}</span>
                            <h1 style={{ fontSize: 36, fontWeight: 800, color: '#fff', marginTop: 4 }}>{project.name}</h1>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 16, marginTop: 12 }}>
                                <span style={{ color: 'rgba(255,255,255,0.7)', fontSize: 14, display: 'flex', alignItems: 'center', gap: 6 }}>📍 {project.location}</span>
                                <span className="badge badge-success">{project.status.charAt(0).toUpperCase() + project.status.slice(1)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section style={{ padding: '48px 0' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 20, marginBottom: 40 }} className="stagger">
                        <div className="stat-card" style={{ textAlign: 'center' }}>
                            <div className="stat-label">Total Economic Value</div>
                            <div className="stat-value" style={{ color: 'var(--primary)' }}>Rp{formatTriliun(project.tev)}T</div>
                        </div>
                        <div className="stat-card" style={{ textAlign: 'center' }}>
                            <div className="stat-label">Total Manfaat</div>
                            <div className="stat-value" style={{ color: 'var(--secondary)' }}>Rp{formatTriliun(project.total_benefits)}T</div>
                        </div>
                        <div className="stat-card" style={{ textAlign: 'center' }}>
                            <div className="stat-label">Total Biaya</div>
                            <div className="stat-value" style={{ color: 'var(--accent)' }}>Rp{formatTriliun(project.total_costs)}T</div>
                        </div>
                        <div className="stat-card" style={{ textAlign: 'center' }}>
                            <div className="stat-label">BCR</div>
                            <div className="stat-value" style={{ color: bcr >= 1 ? 'var(--success)' : 'var(--danger)' }}>{formatRupiah(bcr, 2)}</div>
                        </div>
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 24 }}>
                        <div>
                            <div className="card" style={{ marginBottom: 24 }}>
                                <h2 style={{ fontSize: 18, fontWeight: 700, marginBottom: 20 }}>Komponen Manfaat (Benefits)</h2>
                                {benefits.length ? (
                                    <div className="table-wrapper">
                                        <table className="data-table">
                                            <thead><tr><th>Kategori</th><th>Subkategori</th><th>Deskripsi</th><th>Metode</th><th style={{ textAlign: 'right' }}>Nilai</th></tr></thead>
                                            <tbody>
                                                {benefits.map((b) => (
                                                    <tr key={b.id}>
                                                        <td><span className={`badge ${b.category === 'direct_use' ? 'badge-primary' : b.category === 'indirect_use' ? 'badge-success' : 'badge-purple'}`}>{b.category.replace('_', ' ')}</span></td>
                                                        <td style={{ fontSize: 13 }}>{b.subcategory?.replace('_', ' ')}</td>
                                                        <td style={{ fontSize: 13 }}>{b.description}</td>
                                                        <td><span className="badge badge-info">{b.method_used}</span></td>
                                                        <td style={{ textAlign: 'right', fontWeight: 700, color: 'var(--primary)' }}>Rp{formatRupiah(b.value)}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 24 }}>Belum ada data manfaat.</p>
                                )}
                            </div>

                            <div className="card">
                                <h2 style={{ fontSize: 18, fontWeight: 700, marginBottom: 20 }}>Komponen Biaya (Costs)</h2>
                                {costs.length ? (
                                    <div className="table-wrapper">
                                        <table className="data-table">
                                            <thead><tr><th>Kategori</th><th>Subkategori</th><th>Deskripsi</th><th style={{ textAlign: 'right' }}>Nilai</th></tr></thead>
                                            <tbody>
                                                {costs.map((c) => (
                                                    <tr key={c.id}>
                                                        <td><span className={`badge ${c.category === 'direct_cost' ? 'badge-warning' : 'badge-danger'}`}>{c.category.replace('_', ' ')}</span></td>
                                                        <td style={{ fontSize: 13 }}>{c.subcategory?.replace('_', ' ')}</td>
                                                        <td style={{ fontSize: 13 }}>{c.description}</td>
                                                        <td style={{ textAlign: 'right', fontWeight: 700, color: 'var(--accent)' }}>Rp{formatRupiah(c.value)}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 24 }}>Belum ada data biaya.</p>
                                )}
                            </div>
                        </div>

                        <div>
                            <div className="card" style={{ marginBottom: 24 }}>
                                <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 12 }}>Deskripsi Proyek</h3>
                                <p style={{ fontSize: 14, color: 'var(--text-secondary)', lineHeight: 1.7 }}>{project.description || 'Tidak ada deskripsi.'}</p>
                            </div>

                            <div className="card" style={{ marginBottom: 24 }}>
                                <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 16 }}>Komposisi Manfaat</h3>
                                <div style={{ maxHeight: 200 }}>
                                    <DonutChart labels={benefitLabels} data={benefitData} cutout="60%" showLegend height={200} />
                                </div>
                            </div>

                            {hasLocation && (
                                <div className="card">
                                    <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 12 }}>Lokasi</h3>
                                    <LeafletMap
                                        center={[toNumber(project.latitude), toNumber(project.longitude)]}
                                        zoom={12}
                                        markers={[{ lat: toNumber(project.latitude), lng: toNumber(project.longitude), popupHtml: project.name, openPopup: true }]}
                                        height={200}
                                        style={{ borderRadius: 'var(--radius-sm)', overflow: 'hidden' }}
                                    />
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </GuestLayout>
    );
}
