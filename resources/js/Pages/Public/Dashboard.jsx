import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import DonutChart from '../../Components/charts/DonutChart';
import BarChart from '../../Components/charts/BarChart';
import ProvinceMap from '../../Components/map/ProvinceMap';
import Pagination from '../../Components/ui/Pagination';
import { toNumber, formatTriliun, formatRupiah } from '../../lib/format';

const CATEGORY_COLORS = [
    ['direct_use', '#6366f1', 'Direct Use Value'],
    ['indirect_use', '#06d6a0', 'Indirect Use Value'],
    ['option_value', '#f59e0b', 'Option Value'],
    ['existence_value', '#f72585', 'Existence Value'],
    ['bequest_value', '#8b5cf6', 'Bequest Value'],
];

const METHOD_NAMES = { TCM: 'Travel Cost', CVM: 'Contingent Val.', EOP: 'Effect on Prod.' };

export default function Dashboard({ projects, benefits, methodDistribution, mapProjects }) {
    const totalBen = benefits.reduce((sum, b) => sum + toNumber(b.total), 0);

    const donutData = CATEGORY_COLORS.map(([key]) => {
        const row = benefits.find((b) => b.category === key);
        return toNumber(row?.total);
    });

    const methodLabels = methodDistribution.map((m) => METHOD_NAMES[m.method_used] || m.method_used);
    const methodData = methodDistribution.map((m) => toNumber(m.count));

    return (
        <GuestLayout>
            <Head title="Dashboard Publik — Valuasi Ekonomi" />

            <section style={{ background: 'linear-gradient(135deg,#312e81,#4f46e5)', padding: '60px 0', position: 'relative', overflow: 'hidden' }}>
                <div style={{ position: 'absolute', bottom: 0, right: 0, width: 300, height: 300, background: 'radial-gradient(circle,rgba(6,214,160,0.15),transparent 70%)' }}></div>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px', position: 'relative', zIndex: 1 }} className="animate-fade-up">
                    <div className="badge" style={{ background: 'rgba(255,255,255,0.1)', color: '#fff', marginBottom: 12 }}>📊 Insight Only</div>
                    <h1 style={{ fontSize: 40, fontWeight: 800, color: '#fff', marginBottom: 8 }}>Dashboard Publik</h1>
                    <p style={{ color: 'rgba(255,255,255,0.7)', fontSize: 16 }}>Jelajahi semua proyek valuasi ekonomi yang sudah dipublikasikan</p>
                </div>
            </section>

            <section style={{ padding: '60px 0', background: 'var(--surface-alt)' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 24, marginBottom: 40 }} className="stagger">
                        <div className="card" style={{ padding: 28 }}>
                            <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 24 }}>Komposisi Manfaat berdasarkan Kategori</h3>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 32 }}>
                                <div style={{ width: 200, height: 200, flexShrink: 0 }}>
                                    <DonutChart labels={CATEGORY_COLORS.map((c) => c[2])} data={donutData} colors={CATEGORY_COLORS.map((c) => c[1])} />
                                </div>
                                <div style={{ flex: 1 }}>
                                    {CATEGORY_COLORS.map(([key, color, label]) => {
                                        const row = benefits.find((b) => b.category === key);
                                        const pct = totalBen > 0 ? Math.round(toNumber(row?.total) / totalBen * 100) : 0;
                                        return (
                                            <div key={key} style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 14 }}>
                                                <div style={{ width: 12, height: 12, borderRadius: 3, background: color, flexShrink: 0 }}></div>
                                                <div style={{ flex: 1 }}>
                                                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                                                        <span style={{ fontSize: 13, fontWeight: 500, color: 'var(--text)' }}>{label}</span>
                                                        <span style={{ fontSize: 13, fontWeight: 700, color }}>{pct}%</span>
                                                    </div>
                                                    <div style={{ height: 6, background: '#f1f5f9', borderRadius: 4, overflow: 'hidden' }}>
                                                        <div style={{ height: '100%', width: `${pct}%`, background: color, borderRadius: 4, transition: 'width 1s ease' }}></div>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>

                        <div className="card" style={{ padding: 28 }}>
                            <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 24 }}>Distribusi Metodologi</h3>
                            <div style={{ height: 220 }}>
                                <BarChart labels={methodLabels} data={methodData} />
                            </div>
                        </div>
                    </div>

                    <div style={{ marginBottom: 8 }}>
                        <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 4 }}>Sebaran Proyek per Provinsi</h3>
                        <p style={{ fontSize: 13, color: 'var(--text-secondary)', marginBottom: 20 }}>Klik atau pilih provinsi untuk melihat proyek valuasi yang dipublikasikan di wilayah tersebut.</p>
                        <ProvinceMap projects={mapProjects} height={440} />
                    </div>
                </div>
            </section>

            <section style={{ padding: '60px 0' }}>
                <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 32 }}>
                        <div>
                            <h2 style={{ fontSize: 28, fontWeight: 800 }}>Proyek Valuasi</h2>
                            <p style={{ color: 'var(--text-secondary)', fontSize: 14, marginTop: 4 }}>{projects.total} proyek dipublikasikan</p>
                        </div>
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(360px,1fr))', gap: 20 }} className="stagger">
                        {projects.data.map((project) => (
                            <div className="card" key={project.id} style={{ padding: 0, overflow: 'hidden' }}>
                                <div style={{ padding: '4px 20px 0', background: 'linear-gradient(90deg,var(--primary),var(--secondary))', height: 4 }}></div>
                                <div style={{ padding: 24 }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 12 }}>
                                        <div>
                                            <h3 style={{ fontSize: 16, fontWeight: 700 }}>{project.name}</h3>
                                            <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'monospace' }}>{project.code}</span>
                                        </div>
                                        <span className="badge badge-success">Published</span>
                                    </div>
                                    <p style={{ fontSize: 13, color: 'var(--text-secondary)', marginBottom: 16, lineHeight: 1.6 }}>
                                        {(project.description || '').length > 100 ? project.description.slice(0, 100) + '…' : project.description}
                                    </p>
                                    <div style={{ fontSize: 13, color: 'var(--text-secondary)', marginBottom: 16, display: 'flex', alignItems: 'center', gap: 6 }}>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" /><circle cx="12" cy="10" r="3" /></svg>
                                        {project.location}
                                    </div>
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, paddingTop: 16, borderTop: '1px solid var(--border-light)' }}>
                                        <div>
                                            <div style={{ fontSize: 11, color: 'var(--text-muted)', fontWeight: 600, textTransform: 'uppercase' }}>TEV</div>
                                            <div style={{ fontSize: 20, fontWeight: 800, color: 'var(--primary)' }}>Rp{formatTriliun(project.tev)}T</div>
                                        </div>
                                        <div>
                                            <div style={{ fontSize: 11, color: 'var(--text-muted)', fontWeight: 600, textTransform: 'uppercase' }}>BCR</div>
                                            <div style={{ fontSize: 20, fontWeight: 800, color: 'var(--secondary)' }}>{formatRupiah(project.bcr, 2)}</div>
                                        </div>
                                    </div>
                                    <Link href={route('public.project', project.id)} className="btn btn-outline" style={{ width: '100%', marginTop: 16 }}>Lihat Detail</Link>
                                </div>
                            </div>
                        ))}
                    </div>

                    <Pagination paginator={projects} />
                </div>
            </section>
        </GuestLayout>
    );
}
